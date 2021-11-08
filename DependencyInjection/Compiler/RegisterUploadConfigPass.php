<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\DependencyInjection\Compiler;

use Manuel\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuel\Bundle\UploadDataBundle\Validator\UploadedItemValidator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class RegisterUploadConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('upload_data.config') as $id => $configs) {
            $definition = $container->getDefinition($id);

            foreach ($configs as $config) {
                $showDeleted = isset($config['show_deleted']) ? $config['show_deleted'] : false;
            }

            $this->configureDefinition($definition, $container, $showDeleted);
        }
    }

    protected function configureDefinition(
        Definition $definition,
        ContainerBuilder $container,
        $showDeleted
    ) {
        $definition->addMethodCall('setObjectManager', array(new Reference('doctrine.orm.entity_manager')));
        $definition->addMethodCall('setShowDeleted', array($showDeleted));

        if (!$definition->hasMethodCall('setUploadDir')) {
            $definition->addMethodCall('setUploadDir', array('%upload_data.files_dir%'));
        }

        $definition->addMethodCall('setValidator', array(new Reference(UploadedItemValidator::class)));
        $definition->addMethodCall('setColumnListFactory', array(new Reference('upload_data.column_list_factory')));
        $definition->addMethodCall('setListMapper', array(new Reference('upload_data.list_mapper')));
        $definition->addMethodCall('setReaderLoader', array(new Reference(ReaderLoader::class)));
        $definition->addMethodCall('setTranslator',
            array(new Reference('translator', ContainerInterface::NULL_ON_INVALID_REFERENCE)));
        $definition->addMethodCall('setUploadedFileHelper', array(new Reference('upload_data.file_helper.default')));
        $definition->addMethodCall('setExceptionProfiler', array(new Reference('upload_data.profiler.exception')));
        $definition->addMethodCall('setUrlGenerator', array(new Reference('router')));
        $definition->addMethodCall('initialize');

        $this->fixTemplates($container, $definition);

    }

    protected function fixTemplates(ContainerBuilder $container, Definition $definition)
    {
        $definedTemplates = $container->getParameter('upload_data.templates');

        $methods = array();
        $pos = 0;
        foreach ($definition->getMethodCalls() as $method) {
            if ($method[0] == 'setTemplates') {
                $definedTemplates = array_merge($definedTemplates, $method[1][0]);
                continue;
            }

            if ($method[0] == 'setTemplate') {
                $definedTemplates[$method[1][0]] = $method[1][1];
                continue;
            }

            $methods[$pos] = $method;
            $pos++;
        }

        $definition->setMethodCalls($methods);
        $definition->addMethodCall('setTemplates', array($definedTemplates));
    }
}