<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Data\Reader;

use Manuelj555\Bundle\UploadDataBundle\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class BaseReader implements ReaderInterface
{
    protected $routeConfig;

    /**
     * @param mixed $routeConfig
     */
    public function setRouteConfig($routeConfig)
    {
        $this->routeConfig = $routeConfig;
    }

    /**
     * @return mixed
     */
    public function getRouteConfig()
    {
        return $this->routeConfig;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver, $headers = false)
    {
        if (!$headers) {
            $resolver->setRequired(array('header_mapping'));
        }
    }

    protected function resolveOptions($options, $headers = false)
    {
        $resolver = new OptionsResolver();

        $this->setDefaultOptions($resolver, $headers);

        return $resolver->resolve($options);
    }

    protected function verifyFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $filename));
        }
    }
}