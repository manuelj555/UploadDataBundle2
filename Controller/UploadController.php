<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadedItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/{type}")
 */
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

    /**
     * @Route("/list", name="upload_data_upload_list")
     *
     * @param         $type
     * @param Request $request
     *
     * @return Response
     */
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

    /**
     * @Route("/new", name="upload_data_upload_new")
     *
     * @param         $type
     * @param Request $request
     *
     * @return Response
     */
    public function newAction($type, Request $request)
    {
        $response = null;
        $config = $this->getConfig($type);

        $form = $this->createForm($config->createUploadForm());
        $form->handleRequest($request);

        if ($request->isMethod('POST') and $form->isValid()) {
            $file = $form['file']->getData();

            $this->getConfig($type)
                ->processUpload($file, $form->getData());

            $response = new Response(null, 200, array(
                'X-Reload' => true,
            ));
        }

        return $this->render('@UploadData/Upload/new.html.twig', $this->mergeParams($type, array(
            'form' => $form->createView(),
        )), $response);
    }

    /**
     * @Route("/read/{id}", name="upload_data_upload_read")
     *
     * @param         $type
     * @param Upload  $upload
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function readAction($type, Upload $upload, Request $request)
    {

        $reader = $this->get('upload_data.reader_loader')
            ->get($upload->getFullFilename());

        return $this->redirect($this->generateUrl($reader->getRouteConfig(), array(
            'id' => $upload->getId(),
        )));
    }

    /**
     * @Route("/validate/{id}", name="upload_data_upload_validate")
     *
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function validateAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processValidation($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Validated!');

        return new Response('Ok');
    }

    /**
     * @Route("/transfer/{id}", name="upload_data_upload_transfer")
     *
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function transferAction($type, Upload $upload)
    {
        $this->getConfig($type)
            ->processTransfer($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Transfered!');

        return new Response('Ok');
    }

    /**
     * @Route("/show/{id}", name="upload_data_upload_show")
     *
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function showAction($type, Upload $upload)
    {
        return $this->render('@UploadData/Upload/show.html.twig', $this->mergeParams($type, array(
            'upload' => $upload,
        )));
    }

    /**
     * @Route("/show-item/{id}", name="upload_data_upload_show_item")
     *
     * @param              $type
     * @param UploadedItem $item
     *
     * @return Response
     */
    public function showItemAction($type, UploadedItem $item)
    {
        return $this->render('@UploadData/Upload/show_item.html.twig', $this->mergeParams($type, array(
            'item' => $item,
        )));
    }

    /**
     * @Route("/delete/{id}", name="upload_data_upload_delete")
     *
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function deleteAction($type, Upload $upload)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($upload);
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Deleted!');

        return new Response('Ok');
    }
}
