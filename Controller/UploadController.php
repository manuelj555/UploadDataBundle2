<?php

namespace Manuel\Bundle\UploadDataBundle\Controller;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 */
class UploadController extends Controller
{
    /**
     * @var UploadConfig
     */
    protected $config;
    /**
     * @var string
     */
    protected $type;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->loadConfig($this->getRequestType());
    }

    /**
     * @param string $type
     *
     * @return UploadConfig
     */
    protected function loadConfig($type)
    {
        if ($this->config and $this->type === $type) {
            return $this->config;
        }

        $this->type = $type;

        return $this->config = $this
            ->container
            ->get('upload_data.config_provider')
            ->get($type);
    }

    public function getRequestType()
    {
        return $this->get('request_stack')
            ->getCurrentRequest()
            ->get('type');
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters = array_merge($parameters, array(
            'type' => $this->type,
            'upload_config' => $this->config,
            'config' => $this->config,
        ));

        return parent::render($view, $parameters, $response);
    }

    /**
     * @param         $type
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $query = $this->config->getQueryList($request, $this->get('upload_data.upload_repository'));
//            ->getQuery();

        $items = $this->get('knp_paginator')->paginate($query, $request->get('page', 1));

        return $this->render($this->config->getTemplate('upload_list'), array(
            'items' => $items,
        ));
    }

    /**
     * @param         $type
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $response = null;

        $form = $this->createForm($this->config->createUploadForm());
        $form->handleRequest($request);

        if ($request->isMethod('POST') and $form->isValid()) {
            $file = $form['file']->getData();

            $this->config->processUpload($file, $form->getData());

            $this->addFlash(
                'success',
                $this->get('translator')->trans('label.upload_complete', array(), 'upload_data')
            );

            $this->get('ku_ajax.handler')->success();
            return $this->redirectToRoute('upload_data_upload_list', array(
                'type' => $this->getRequestType(),
            ));
        }

        return $this->render($this->config->getTemplate('upload_new'), array(
            'form' => $form->createView(),
        ), $response);
    }

    /**
     * @param         $type
     * @param Upload  $upload
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function readAction(Upload $upload, Request $request)
    {
        $reader = $this->get('upload_data.reader_loader')
            ->get($upload->getFullFilename());

        return $this->redirectToRoute($reader->getRouteConfig(),  array(
            'id' => $upload->getId(),
        ));
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function validateAction(Upload $upload)
    {
        $this->config->processValidation($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Validated!');

        return new Response('Ok');
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function transferAction(Upload $upload)
    {
        $this->config->processTransfer($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Transfered!');

        return new Response('Ok');
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function showAction(Upload $upload)
    {
        return $this->render($this->config->getTemplate('upload_show'), array(
            'upload' => $upload,
        ));
    }

    /**
     * @param              $type
     * @param UploadedItem $item
     *
     * @return Response
     */
    public function showItemAction($type, UploadedItem $item)
    {
        return $this->render($this->config->getTemplate('upload_show_item'), array(
            'item' => $item,
        ));
    }

    /**
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
