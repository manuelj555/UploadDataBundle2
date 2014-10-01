<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Data\Reader;

use Manuelj555\Bundle\UploadDataBundle\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
interface ReaderInterface
{
    const EXTRA_FIELDS_NAME = '__EXTRA__';

    public function getData($filename, $options);
    public function getRowHeaders($filename, $options);

    public function supports($filename);
    public function setDefaultOptions(OptionsResolverInterface $resolver, $headers = false);
    public function setRouteConfig($route);
    public function getRouteConfig();
}