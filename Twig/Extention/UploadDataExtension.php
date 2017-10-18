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

    public function getTests()
    {
        return [
            new \Twig_SimpleTest('upload_transfered', [$this, 'isUploadTransfered']),
            new \Twig_SimpleTest('upload_validated', [$this, 'isUploadValidated']),
            new \Twig_SimpleTest('upload_readed', [$this, 'isUploadReaded']),
        ];
    }

    public function isUploadTransfered(Upload $upload)
    {
        return $this->isActionCompleted($upload, 'transfer');
    }

    public function isUploadValidated(Upload $upload)
    {
        return $this->isActionCompleted($upload, 'validate');
    }

    public function isUploadReaded(Upload $upload)
    {
        return $this->isActionCompleted($upload, 'read');
    }

    /**
     * @param Upload $upload
     * @param $actionName
     * @return bool
     */
    private function isActionCompleted(Upload $upload, $actionName)
    {
        return $upload->getAction($actionName)->isComplete();
    }
}