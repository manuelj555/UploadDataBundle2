<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ConfigProvider
{
    protected $configs = array();
    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct($container, $configs)
    {
        $this->container = $container;
        $this->configs = $configs;
    }

    /**
     * @param $type
     *
     * @return UploadConfig
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        if (!$this->has($type)){
            throw new \InvalidArgumentException(sprintf('Tipo "%s" no definido', $type));
        }

        $config = $this->container->get($this->configs[$type]);

        $config->processConfiguration();

        return $config;
    }

    public function has($type)
    {
        return isset($this->configs[$type]);
    }
} 