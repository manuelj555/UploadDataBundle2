<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Psr\Container\ContainerInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ConfigProvider
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        if (!$this->container->has($type)){
            throw new \InvalidArgumentException(sprintf('Tipo "%s" no definido', $type));
        }

        $config = $this->container->get($type);

        $config->processConfiguration($options);

        return $config;
    }

    public function has($type)
    {
        return isset($this->configs[$type]);
    }
} 