<?php
/**
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface UploadedFileHelperInterface
 *
 * @author Manuel Aguirre maguirre@optimeconsulting.com
 */
interface UploadedFileHelperInterface
{
    /**
     * Esta función se encarga de preparar el archivo para su lectura.
     * Puede ser moverlo al sitio de lectura, crearlo, darle permisos de lectura, etc.
     *
     * @param Upload $upload
     */
    public function prepareFileForRead(Upload $upload);

    /**
     * Se encarga de guardar el archivo en donde corresponda.
     *
     * @return File
     */
    public function saveFile(UploadedFile $file, $path , $filename);
}