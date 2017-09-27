<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class RegisterUploadReaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $readerLoader = $container->findDefinition('upload_data.reader_loader');

        foreach ($container->findTaggedServiceIds('upload_data.reader') as $id => $configs) {
            $definition = $container->getDefinition($id);
            $route = null;

            foreach ($configs as $config) {

                if (!isset($config['route_config'])) {
                    $this->throwAttribute('route_config', $id);
                }

                $route = $config['route_config'];

                $readerLoader->addMethodCall('addReader', array(new Reference($id)));
            }

            $this->configureDefinition($route, $definition, $container);
        }
    }

    protected function throwAttribute($attribute, $service)
    {
        throw new \InvalidArgumentException(sprintf('El servicio "%s" debe tener definido el atributo "%s" para el tag "upload_data.config".', $service, $attribute));
    }

    protected function configureDefinition($route, Definition $definition, ContainerBuilder $container)
    {
        $definition->addMethodCall('setRouteConfig', array($route));
        $definition->addMethodCall(
            'setUploadedFileHelper',
            array(new Reference('upload_data.file_helper.default'))
        );
    }
}