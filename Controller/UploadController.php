<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    /**
     * @param string $type
     *
     * @return UploadConfig
     */
    protected function getConfig($type)
    {
        return $this->container->get('upload_data.config_provider')
            ->get($type);
    }

    protected function mergeParams($type, $params = array())
    {
        return array_merge($params, array(
            'upload_config' => $this->getConfig($type)
        ));
    }

    public function listAction($type)
    {
        $items = $this->getDoctrine()
            ->getRepository('UploadDataBundle:Upload')
            ->getQueryForType($type)
            ->getQuery()
            ->getResult();

        return $this->render('@UploadData/Upload/list.html.twig', $this->mergeParams($type, array(
            'items' => $items,
        )));
    }

    public function newAction($type, Request $request)
    {
        if ($request->isMethod('POST') and $request->files->has('file')) {
            $file = $request->files->get('file');

            $this->getConfig($type)
                ->processUpload($file);
        }

        return $this->render('@UploadData/Upload/new.html.twig', $this->mergeParams($type, array()));
    }

    public function readAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processRead($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Readed!');

        return new Response('Ok');
    }

    public function validateAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processValidation($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Validated!');

        return new Response('Ok');
    }

    public function transferAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processTransfer($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Transfered!');

        return new Response('Ok');
    }

    public function showAction($type, Upload $upload)
    {
        return $this->render('@UploadData/Upload/show.html.twig', $this->mergeParams($type, array(
            'upload' => $upload,
            'type' => $type,
        )));
    }
}
