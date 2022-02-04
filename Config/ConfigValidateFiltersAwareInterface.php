<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Config;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;

/**
 * @author Manuel Aguirre
 */
interface ConfigValidateFiltersAwareInterface
{
    public function onPreValidate(Upload $upload): void;

    public function onPostValidate(Upload $upload): void;
}