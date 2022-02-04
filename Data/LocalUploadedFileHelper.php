<?php
/**
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LocalUploadedFileHelper
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class LocalUploadedFileHelper implements UploadedFileHelperInterface
{
    public function prepareFileForRead(string $filename): string
    {
        return $filename;
    }

    public function saveFile(UploadedFile $file, string $path, string $filename): string
    {
        return $file->move($path, $filename)->getRealPath();
    }
}