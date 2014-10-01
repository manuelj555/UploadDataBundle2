<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            'upload_config' => $this->getConfig($type),
            'type' => $type,
        ));
    }

    public function listAction($type, Request $request)
    {
        $query = $this->getDoctrine()
            ->getRepository('UploadDataBundle:Upload')
            ->getQueryForType($type);
//            ->getQuery();

        $items = $this->get('knp_paginator')->paginate($query, $request->get('page', 1));

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

    public function readAction($type, Upload $upload, Request $request)
    {

        $reader = $this->get('upload_data.reader_loader')
            ->get($upload->getFullFilename());

        return $this->redirect($this->generateUrl($reader->getRouteConfig(), array(
            'id' => $upload->getId(),
        )));
//
//        if ($request->isMethod('POST')) {
//            $this->getConfig($type)
//                ->processRead($upload);
//
//            return new Response('Ok');
//        }
//
//        return $this->render('@UploadData/Upload/read.html.twig', $this->mergeParams($type, array(
//            'upload' => $upload,
//        )));
    }

    public function validateAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processValidation($upload);

//        $this->get('session')
//            ->getFlashBag()
//            ->add('success', 'Validated!');

        return new Response('Ok');
    }

    public function transferAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processTransfer($upload);

//        $this->get('session')
//            ->getFlashBag()
//            ->add('success', 'Transfered!');

        return new Response('Ok');
    }

    public function showAction($type, Upload $upload)
    {
        return $this->render('@UploadData/Upload/show.html.twig', $this->mergeParams($type, array(
            'upload' => $upload,
        )));
    }

    public function showItemAction($type, UploadedItem $item)
    {
        return $this->render('@UploadData/Upload/show_item.html.twig', $this->mergeParams($type, array(
            'item' => $item,
        )));
    }
}
