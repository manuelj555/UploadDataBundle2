<?php

namespace Manuel\Bundle\UploadDataBundle\Controller;

use Manuel\Bundle\UploadDataBundle\Entity\Upload;
use Manuel\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Manuel\Bundle\UploadDataBundle\Form\Type\AttributeType;
use Manuel\Bundle\UploadDataBundle\Form\Type\CsvConfigurationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 */
class CsvReadController extends BaseReadController
{

    /**
     * @param Upload $upload
     */
    public function separatorAction(Request $request, Upload $upload)
    {
        $config = $this->getConfig($upload);

        if (!$separatorAttribute = $upload->getAttribute('separator')) {
            $separatorAttribute = new UploadAttribute('separator', '|');
            $upload->addAttribute($separatorAttribute);
        }

        $separatorAttribute->setFormLabel('Caracter separador');

        $form = $this->createFormBuilder()
            ->setAction($request->getRequestUri())
            ->add('attributes', 'collection', array(
                'type' => new AttributeType(),
                'data' => array($separatorAttribute),
            ))
            ->add('enviar', 'submit', array(
                'attr' => array('class' => 'btn-primary'),
                'label' => 'Siguiente Paso',
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();

            return $this->redirect($this->generateUrl('upload_data_upload_select_columns_csv', array(
                'id' => $upload->getId(),
            )));
        }

        return $this->render($config->getTemplate('read_csv_separator'), array(
            'form' => $form->createView(),
            'config' => $config,
        ));
    }

    /**
     * @param Request $request
     * @param Upload  $upload
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectColumnsAction(Request $request, Upload $upload)
    {
        $config = $this->getConfig($upload);
        //la idea acá es leer las columnas del archivo y mostrarlas
        //para que el usuario haga un mapeo de ellas con las esperadas
        //por el sistema.

        $options = array(
            'delimiter' => $upload->getAttribute('separator')->getValue(),
            'row_headers' => 0,
        );

        $headers = $this->get('upload_data.csv_reader')
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
