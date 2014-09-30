<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Manuelj555\Bundle\UploadDataBundle\Entity\UploadAttribute;
use Manuelj555\Bundle\UploadDataBundle\Form\Type\CsvConfigurationType;
use Symfony\Component\HttpFoundation\Request;

class CsvReadController extends BaseReadController
{

    public function separatorAction(Request $request, Upload $upload)
    {
        if (!$separatorAttribute = $upload->getAttribute('separator')) {
            $separatorAttribute = new UploadAttribute('separator', '|');
            $upload->addAttribute($separatorAttribute);
        }

        $separatorAttribute->setFormLabel('Caracter separador');


        $form = $this->createForm(new CsvConfigurationType(), $upload, array(
            'action' => $request->getRequestUri(),
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($upload);
            $em->flush();

            return $this->redirect($this->generateUrl('upload_data_upload_select_columns_csv', array(
                'id' => $upload->getId(),
            )));
        }

        return $this->render('@UploadData/Read/Csv/separator.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
