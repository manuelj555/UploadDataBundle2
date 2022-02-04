<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;

/**
 * @author Manuel Aguirre
 */
interface ConfigReadFiltersAwareInterface
{
    public function onPreRead(Upload $upload): void;

    public function onPostRead(Upload $upload): void;
}