<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

            foreach ($configs as $config) {

                if (!isset($config['id'])) {
                    $this->throwAttribute('id', $id);
                }

                if (!isset($config['label'])) {
                    $this->throwAttribute('label', $id);
                }

                if (isset($uploadTypes[$config['id']])) {
                    throw new \InvalidArgumentException(sprintf('Ya se ha definido el nombre "%s" previamente por el servicio "%s".', $config['id'], $uploadTypesServices[$config['id']]));
                }

                $type = $config['id'];

                $uploadTypes[$config['id']] = $config['label'];
                $uploadTypesServices[$config['id']] = $id;
            }

            $this->configureDefinition($type, $definition, $container);
        }

        $container->setParameter('upload_data.upload_types', $uploadTypes);

        $container->findDefinition('upload_data.config_provider')
            ->replaceArgument(1, $uploadTypesServices);
    }

    protected function throwAttribute($attribute, $service)
    {
        throw new \InvalidArgumentException(sprintf('El servicio "%s" debe tener definido el atributo "%s" para el tag "upload_data.config".', $service, $attribute));
    }

    protected function configureDefinition($type, Definition $definition, ContainerBuilder $container)
    {
        $definition->addMethodCall('setObjectManager', array(new Reference('doctrine.orm.entity_manager')));
        $definition->addMethodCall('setUploadDir', array('%upload_data.files_dir%'));
        $definition->addMethodCall('setType', array($type));
    }
}