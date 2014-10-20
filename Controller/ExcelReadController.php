<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Manuelj555\Bundle\UploadDataBundle\Form\Type\AttributeType;
use Manuelj555\Bundle\UploadDataBundle\Form\Type\CsvConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/read/xls/{id}")
 */
class ExcelReadController extends BaseReadController
{

    /**
     * @Route("/select-row", name="upload_data_upload_read_excel")
     *
     * @param Request $request
     * @param Upload  $upload
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function selectRowHeadersAction(Request $request, Upload $upload)
    {
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
            ->add('attributes', 'collection', array(
                'type' => new AttributeType(),
                'data' => array($attr),
            ))
            ->add('preview', 'button', array(
                'attr' => array('class' => 'btn-info'),
                'label' => 'button.preview_row',
            ))
            ->add('send', 'submit', array(
                'attr' => array('class' => 'btn-primary'),
                'label' => 'button.next_step',
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();

            return $this->redirect($this->generateUrl('upload_data_upload_select_columns_excel', array(
                'id' => $upload->getId(),
            )));
        }

        return $this->render('@UploadData/Read/Excel/select_row_headers.html.twig', array(
            'form' => $form->createView(),
            'upload' => $upload,
        ));
    }

    /**
     * @Route("/preview-headers", name="upload_data_upload_read_excel_preview_headers")
     *
     * @param Request $request
     * @param Upload  $upload
     *
     * @return Response
     */
    public function previewHeadersAction(Request $request, Upload $upload)
    {
        $row = $request->get('row', 1);

        //previsualizamos las cabeceras
        $headers = $this->get('upload_data.excel_reader')
            ->getRowHeaders($upload->getFullFilename(), array(
                'row_headers' => $row,
            ));

        return $this->render('@UploadData/Read/Excel/preview_headers.html.twig', array(
            'headers' => $headers,
        ));
    }

    /**
     * @Route("/select-columns", name="upload_data_upload_select_columns_excel")
     *
     * @param Request $request
     * @param Upload  $upload
     *
     * @return Response
     */
    public function selectColumnsAction(Request $request, Upload $upload)
    {
        //la idea acá es leer las columnas del archivo y mostrarlas
        //para que el usuario haga un mapeo de ellas con las esperadas
        //por el sistema.

        $options = array(
            'row_headers' => $upload->getAttribute('row_headers')->getValue(),
        );

        $headers = $this->get('upload_data.reader_loader')
            ->get($upload->getFullFilename())
            ->getRowHeaders($upload->getFullFilename(), $options);

        $a = $this->getConfig($upload);
        $columnsMapper = $a->getColumnsMapper();

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

            $this->processRead($upload, $options);

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Readed!');

            return Response::create('Ok', 203, array(
                'X-Close-Modal' => true,
                'X-Reload' => true,
            ));
        }

        return $this->render('@UploadData/Read/select_columns.html.twig', array(
            'file_headers' => $headers,
            'columns' => $columns,
            'matches' => $matches,
        ));
    }
}
