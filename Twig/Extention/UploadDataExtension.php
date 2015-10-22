<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Twig\Extention;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnList\LoadedColumn;


/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadDataExtension extends \Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'upload_data.callback_extension';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('upload_item_value', array($this, 'getUploadAttributeValue'))
        );
    }

    public function getUploadAttributeValue(Upload $upload, LoadedColumn $column, $attribute)
    {

    }
}