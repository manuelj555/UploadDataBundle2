<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Metadata;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ReaderLoader
{
    protected $readers = array();

    public function addReader(ReaderInterface $reader)
    {
        $this->readers[] = $reader;
    }

    /**
     * @param $filename
     *
     * @return null|ReaderInterface
     */
    public function get($filename)
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($filename)) {
                return $reader;
            }
        }
    }
}