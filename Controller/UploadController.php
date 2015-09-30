<?php

namespace Manuel\Bundle\UploadDataBundle\Controller;

use Manuel\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAction;
use Manuel\Bundle\UploadDataBundle\Entity\UploadedItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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
        $filterForm = $this->createFilterListForm();
        $filterForm->handleRequest($request);

        $query = $this->config->getQueryList(
            $this->get('upload_data.upload_repository'), $filterForm->getData()
        );

        $items = $this->get('knp_paginator')->paginate($query, $request->get('page', 1), 1);

        return $this->render($this->config->getTemplate('list'), array(
            'items' => $items,
            'filter_form' => $filterForm->createView(),
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

        return $this->render($this->config->getTemplate('new'), array(
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

        return $this->redirectToRoute($reader->getRouteConfig(), array(
            'id' => $upload->getId(),
        ));
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function validateAction(Request $request, Upload $upload, $type)
    {
        $this->config->processValidation($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Validated!');

        return $this->redirectToTargetOrList($request, $type);
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function transferAction(Request $request, Upload $upload, $type)
    {
        $this->config->processTransfer($upload);

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Transfered!');

        return $this->redirectToTargetOrList($request, $type);
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function showAction(Request $request, Upload $upload)
    {
        $query = $this->getDoctrine()
            ->getRepository('UploadDataBundle:UploadedItem')
            ->getQueryByUpload($upload);

        $pagination = $this->get('knp_paginator')
            ->paginate($query, $request->get('page', 1), $request->get('per_page', 10));

        return $this->render($this->config->getTemplate('show'), array(
            'upload' => $upload,
            'pagination' => $pagination,
        ));
    }

    /**
     * @param              $type
     * @param UploadedItem $item
     *
     * @return Response
     */
    public function showItemAction(UploadedItem $item)
    {
        return $this->render($this->config->getTemplate('show_item'), array(
            'item' => $item,
        ));
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function deleteAction(Request $request, Upload $upload, $type)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($upload);
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Deleted!');

        return $this->redirectToRoute('upload_data_upload_list', array_merge(
            array('type' => $type)
        ));
    }

    /**
     * @param        $type
     * @param Upload $upload
     *
     * @return Response
     */
    public function restoreInProgressAction($type, Upload $upload)
    {
        /** @var UploadAction $action */
        foreach ($upload->getActions() as $action) {
            if ($action->isInProgress()) {
                $action->setNotComplete();
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->add('success', 'Restored!');

        return $this->redirectToRoute('upload_data_upload_show', array('type' => $type, 'id' => $upload->getId()));
    }

    protected function createFilterListForm()
    {
        return $this->get('form.factory')->createNamedBuilder('filter', 'form', null, array(
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => array('class' => 'upload_filter_form'),
        ))
            ->add('search', 'text', array('required' => false))
            ->getForm();
    }

    protected function attachException(Upload $upload, $actionName)
    {
        $action = $upload->getAction($actionName);
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();

        $closure = function (GetResponseForExceptionEvent $event) use ($upload, $action, $em) {
            if ($em->isOpen() and $action and $action->isInProgress()) {
                $action->setNotComplete();
                $em->persist($action);
                $em->flush($action);
            }
        };

        $this->get('event_dispatcher')->addListener(KernelEvents::EXCEPTION, $closure);
    }

    protected function redirectToTargetOrList(Request $request, $type)
    {
        $parameters = array(
            'type' => $type,
            'filter' => $request->get('filter'),
            'page' => $request->get('page'),
        );

        switch ($request->get('_target')) {
            case 'show':
                $route = 'upload_data_upload_show';
                $parameters['id'] = $request->get('id');
                break;
            default:
                $route = 'upload_data_upload_list';
        }

        return $this->redirectToRoute($route, array_filter($parameters));
    }
}
