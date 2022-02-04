<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Manuel Aguirre
 */
interface ConfigUploadFiltersAwareInterface
{
    public function onPreUpload(Upload $upload, File $file, array $formData = []): void;

    public function onPostUpload(Upload $upload, string $filename, array $formData = []): void;
}