<?php
/**
 * 29/09/14
 * upload
 */

namespace Manuelj555\Bundle\UploadDataBundle\Data\Reader;

use Manuelj555\Bundle\UploadDataBundle\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class CacheReader implements ReaderInterface
{

    public function getData($filename, Metadata $meta)
    {

    }

    public function supports($filename)
    {
        // TODO: Implement supports() method.
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // TODO: Implement setDefaultOptions() method.
    }
}