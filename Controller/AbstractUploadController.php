<?php
/**
 * @author Manuel Aguirre
 */

namespace Manuel\Bundle\UploadDataBundle\Controller;

use Manuel\Bundle\UploadDataBundle\Config\ConfigHelper;
use Manuel\Bundle\UploadDataBundle\Config\ConfigHelperFactory;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ExcelHeadersMatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @author Manuel Aguirre
 */
class AbstractUploadController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() + [
                'config_helper_factory' => ConfigHelperFactory::class,
                'headers_matcher' => ExcelHeadersMatcher::class,
            ];
    }

    protected function getHelper(string $configClass): ConfigHelper
    {
        return $this->get('config_helper_factory')->createForType($configClass);
    }

    protected function getColumnsMatcher(): ExcelHeadersMatcher
    {
        return $this->get('headers_matcher');
    }
}