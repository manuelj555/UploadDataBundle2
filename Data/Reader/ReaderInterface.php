<?php
/**
 * 27/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data\Reader;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
interface ReaderInterface
{
    const EXTRA_FIELDS_NAME = '__EXTRA__';

    public function getData(Upload $upload): array;

    public function getHeaders(Upload $upload): array;

    public function supports(Upload $upload): bool;

    public function configureOptions(OptionsResolver $resolver, bool $headers = false): void;
}