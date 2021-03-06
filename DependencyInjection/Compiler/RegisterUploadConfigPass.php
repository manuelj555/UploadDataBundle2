<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\DependencyInjection\Compiler;

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
        $uploadTypes = array();
        $uploadTypesServices = array();

        foreach ($container->findTaggedServiceIds('upload_data.config') as $id => $configs) {
            $definition = $container->getDefinition($id);
            $type = null;
            $label = null;

            foreach ($configs as $config) {

                if (!isset($config['id'])) {
                    $this->throwAttribute('id', $id);
                }

                if (!isset($config['label'])) {
                    $this->throwAttribute('label', $id);
                }

                if (isset($uploadTypes[$config['id']])) {
                    throw new \InvalidArgumentException(sprintf('Ya se ha definido el nombre "%s" previamente por el servicio "%s".',
                        $config['id'], $uploadTypesServices[$config['id']]));
                }

                $showDeleted = isset($config['show_deleted']) ? $config['show_deleted'] : false;
                $type = $config['id'];
                $label = $config['label'];

                $uploadTypes[$config['id']] = $config['label'];
                $uploadTypesServices[$config['id']] = $id;
            }

            $this->configureDefinition($type, $definition, $container, $label, $showDeleted);
        }

        $container->setParameter('upload_data.upload_types', $uploadTypes);

        $container->findDefinition('upload_data.config_provider')
            ->replaceArgument(1, $uploadTypesServices);
    }

    protected function throwAttribute($attribute, $service)
    {
        throw new \InvalidArgumentException(sprintf('El servicio "%s" debe tener definido el atributo "%s" para el tag "upload_data.config".',
            $service, $attribute));
    }

    protected function configureDefinition(
        $type,
        Definition $definition,
        ContainerBuilder $container,
        $label,
        $showDeleted
    ) {
        $definition->addMethodCall('setLabel', array($label));
        $definition->addMethodCall('setObjectManager', array(new Reference('doctrine.orm.entity_manager')));
        $definition->addMethodCall('setShowDeleted', array($showDeleted));

        if (!$definition->hasMethodCall('setUploadDir')) {
            $definition->addMethodCall('setUploadDir', array('%upload_data.files_dir%'));
        }

        $definition->addMethodCall('setType', array($type));
        $definition->addMethodCall('setValidator', array(new Reference('validator')));
        $definition->addMethodCall('setColumnListFactory', array(new Reference('upload_data.column_list_factory')));
        $definition->addMethodCall('setListMapper', array(new Reference('upload_data.list_mapper')));
        $definition->addMethodCall('setReaderLoader', array(new Reference('upload_data.reader_loader')));
        $definition->addMethodCall('setTranslator',
            array(new Reference('translator', ContainerInterface::NULL_ON_INVALID_REFERENCE)));
        $definition->addMethodCall('setUploadedFileHelper', array(new Reference('upload_data.file_helper.default')));
        $definition->addMethodCall('setExceptionProfiler', array(new Reference('upload_data.profiler.exception')));
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