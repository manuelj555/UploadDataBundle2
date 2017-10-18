<?php
/**
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LocalUploadedFileHelper
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
class LocalUploadedFileHelper implements UploadedFileHelperInterface
{
    public function prepareFileForRead($filename)
    {
        return $filename;
    }

    public function saveFile(UploadedFile $file, $path, $filename)
    {
        return $file->move($path, $filename)->getRealPath();
    }
}