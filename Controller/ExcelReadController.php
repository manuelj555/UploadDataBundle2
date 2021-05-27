<?php

namespace Manuel\Bundle\UploadDataBundle\Controller;

use Manuel\Bundle\UploadDataBundle\Data\Reader\ReaderLoader;
use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Manuel\Bundle\UploadDataBundle\Form\Type\AttributeType;
use Manuel\Bundle\UploadDataBundle\Form\Type\CsvConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 */
class ExcelReadController extends BaseReadController
{

    /**
     * @param Request $request
     * @param Upload  $upload
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function selectRowHeadersAction(Request $request, Upload $upload)
    {
        $config = $this->getConfig($upload);

        if (!$attr = $upload->getAttribute('row_headers')) {
            $attr = new UploadAttribute('row_headers', 1);
            $upload->addAttribute($attr);
        }

        $attr->setFormLabel('label.header_number_row');

        $form = $this->createFormBuilder(null, array(
            'translation_domain' => 'upload_data'
        ))
            ->setAction($request->getRequestUri())
            ->setMethod('post')
            ->add('attributes', CollectionType::class, array(
                'entry_type' => AttributeType::class,
                'data' => array($attr),
            ))
            ->add('preview', ButtonType::class, array(
                'attr' => array('class' => 'btn-info'),
                'label' => 'button.preview_row',
            ))
            ->add('send', SubmitType::class, array(
                'attr' => array('class' => 'btn-primary'),
                'label' => 'button.next_step',
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();

            return $this->redirect($this->generateUrl('upload_data_upload_select_columns_excel', array(
                'id' => $upload->getId(),
            )));
        }

        return $this->render($config->getTemplate('read_excel_select_row_headers'), array(
            'form' => $form->createView(),
            'upload' => $upload,
            'config' => $config,
        ));
    }

    /**
     * @param Request $request
     * @param Upload  $upload
     *
     * @return Response
     */
    public function previewHeadersAction(Request $request, Upload $upload)
    {
        $config = $this->getConfig($upload);

        $row = $request->get('row', 1);

        //previsualizamos las cabeceras
        $headers = $this->get('upload_data.excel_reader')
            ->getRowHeaders($upload->getFullFilename(), array(
                'row_headers' => $row,
            ));

        return $this->render($config->getTemplate('read_excel_preview_headers'), array(
            'headers' => $headers,
            'config' => $config,
        ));
    }

    /**
     * @param Request $request
     * @param Upload  $upload
     *
     * @return Response
     */
    public function selectColumnsAction(Request $request, Upload $upload, ReaderLoader $readerLoader)
    {
        $config = $this->getConfig($upload);
        //la idea acá es leer las columnas del archivo y mostrarlas
        //para que el usuario haga un mapeo de ellas con las esperadas
        //por el sistema.

        $options = array(
            'row_headers' => $upload->getAttribute('row_headers')->getValue(),
        );

        $headers = $readerLoader->get($upload->getFullFilename())
            ->getRowHeaders($upload->getFullFilename(), $options);

        $columnsMapper = $config->getColumnsMapper();

        $columns = $columnsMapper->getColumns();
        $matches = $columnsMapper->match($headers);

        if ($request->isMethod('POST') and $request->request->has('columns')) {

            $options['header_mapping'] = $columnsMapper
                ->mapForm($request->request->get('columns'), $headers);

            if ($attr = $upload->getAttribute('config_read')) {
                $attr->setValue($options);
            } else {
                $upload->addAttribute(new UploadAttribute('config_read', $options));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();

            if ($this->processRead($upload, $options)) {
                $this->addFlash('success', 'Readed!');
            }

            return Response::create('Ok', 203, array(
                'X-Close-Modal' => true,
                'X-Reload' => true,
            ));
        }

        return $this->render($config->getTemplate('read_select_columns'), array(
            'file_headers' => $headers,
            'columns' => $columns,
            'matches' => $matches,
            'config' => $config,
        ));
    }
}
