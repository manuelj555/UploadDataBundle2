<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
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
     * @param string $type
     * @param array $options
     *
     * @return UploadConfig
     * @throws \InvalidArgumentException
     */
    public function get($type, $options = [])
    {
        if (!$this->has($type)){
            throw new \InvalidArgumentException(sprintf('Tipo "%s" no definido', $type));
        }

        $config = $this->container->get($this->configs[$type]);

        $config->processConfiguration($options);

        return $config;
    }

    public function has($type)
    {
        return isset($this->configs[$type]);
    }
} 