<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Data\UploadedFileHelperInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
abstract class BaseReader implements ReaderInterface
{

    /**
     * @var string
     */
    protected $routeConfig;

    /**
     * @var UploadedFileHelperInterface
     */
    protected $uploadedFileHelper;

    /**
     * @param mixed $routeConfig
     */
    public function setRouteConfig($routeConfig)
    {
        $this->routeConfig = $routeConfig;
    }

    /**
     * @param UploadedFileHelperInterface $uploadedFileHelper
     */
    public function setUploadedFileHelper($uploadedFileHelper)
    {
        $this->uploadedFileHelper = $uploadedFileHelper;
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

    protected function resolveFile($filename)
    {
        $filename = $this->uploadedFileHelper->prepareFileForRead($filename);

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $filename));
        }

        return $filename;
    }
}