<?php

namespace Manuel\Bundle\UploadDataBundle\Controller;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\ConfigProvider;
use Manuel\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;

/**
 * @Route(condition="request.isXmlHttpRequest()")
 */
class BaseReadController extends Controller
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * BaseReadController constructor.
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @return UploadConfig
     */
    protected function getConfig(Upload $upload)
    {
        return $this->configProvider->get($upload->getType());
    }

    protected function processRead(Upload $upload)
    {
        $config = $this->getConfig($upload);
        try {
            $config->processRead($upload);

            return true;
        } catch (\Exception $e) {

            if ($this->container->has('logger')) {
                $this->get('logger')->critical('No se pudo procesar la lectura del excel', array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ));
            }

            $this->addFlash('error', 'there has been an error, we could not complete the operation!');
        }

        return false;
    }
}
