<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use InvalidArgumentException;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use function dd;
use function iterator_to_array;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class ReaderLoader
{
    private iterable $readers;

    public function __construct(
        #[TaggedIterator('upload_data.reader')] iterable $readers
    ) {
        $this->readers = $readers;
    }

    public function get(Upload $upload): ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($upload)) {
                return $reader;
            }
        }

        throw new InvalidArgumentException("No se encontro un reader para el upload #" . $upload->getId());
    }
}