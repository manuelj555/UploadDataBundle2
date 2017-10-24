<?php
/**
 * 28/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Twig\Extention;

use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Mapper\ColumnList\LoadedColumn;

/**
 * @autor Manuel Aguirre <programador.manuel@gmail.com>
 */
class UploadDataExtension extends \Twig_Extension
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * UploadDataExtension constructor.
     *
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

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
            new \Twig_SimpleTest('upload_action_actionable', [$this, 'isActionActionable']),
            new \Twig_SimpleTest('upload_action_completed', [$this, 'isActionCompleted']),
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
    public  function isActionCompleted(Upload $upload, $actionName)
    {
        $action = $upload->getAction($actionName);

        return $action && $action->isComplete();
    }

    /**
     * @param Upload $upload
     * @param $actionName
     * @return bool
     */
    public function isActionActionable(Upload $upload, $actionName)
    {
        $config = $this->configProvider->get($upload->getType());

        return $config->isActionable($upload, $actionName);
    }
}