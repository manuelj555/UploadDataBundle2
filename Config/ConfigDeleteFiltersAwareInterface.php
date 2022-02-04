<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;

/**
 * @author Manuel Aguirre
 */
interface ConfigDeleteFiltersAwareInterface
{
    public function onPreDelete(Upload $upload): void;

    public function onPostDelete(Upload $upload): void;
}