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
class RegisterColumnListPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $types = array();

        foreach ($container->findTaggedServiceIds('upload_data.column_list') as $id => $configs) {
            $definition = $container->getDefinition($id);

            foreach ($configs as $config) {

                if (!isset($config['alias'])) {
                    $this->throwAttribute('alias', $id);
                }

                $types[$config['alias']] = $id;
            }

//            $this->configureDefinition($type, $definition, $container);
        }

//        $container->setParameter('upload_data.column_list.types', $types);

        $container->findDefinition('upload_data.column_list_factory')
            ->replaceArgument(1, $types);
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
        $definition->addMethodCall('setValidator', array(new Reference('validator')));
    }
}