<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;

class BaseReadController extends Controller
{
    /**
     * @return UploadConfig
     */
    protected function getConfig(Upload $upload)
    {
        return $this->container->get('upload_data.config_provider')
            ->get($upload->getType());
    }

    protected function processRead(Upload $upload)
    {
        $config = $this->getConfig($upload);

        $this->get('event_dispatcher')->addListener('kernel.terminate'
            , function () use ($config, $upload) {
                $config->processRead($upload);
            });
    }
}
