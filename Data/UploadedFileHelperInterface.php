<?php
/**
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Data;

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
     * @param string $filename
     * @return string la ruta al archivo que será leido.
     */
    public function prepareFileForRead(string $filename): string;

    /**
     * Se encarga de guardar el archivo en donde corresponda.
     */
    public function saveFile(UploadedFile $file, string $path, string $filename): string;
}