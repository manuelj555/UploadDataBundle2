<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Manuelj555\Bundle\UploadDataBundle\Config\UploadConfig;
use Manuelj555\Bundle\UploadDataBundle\Entity\Upload;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function selectColumnsAction(Request $request, Upload $upload)
    {
        //la idea acá es leer las columnas del archivo y mostrarlas
        //para que el usuario haga un mapeo de ellas con las esperadas
        //por el sistema.

        $options = array(
            'delimiter' => $upload->getAttribute('separator')->getValue(),
            'row_headers' => 0,
        );

        $headers = $this->get('upload_data.csv_reader')
            ->getRowHeaders($upload->getFullFilename(), $options);

        $a = $this->getConfig($upload);
        $columnsMapper = $a->getColumnsMapper();

        $columns = $columnsMapper->getColumns();
        $matches = $columnsMapper->match($headers);

        if ($request->isMethod('POST') and $request->request->has('columns')) {
            $this->processRead($upload
                , $columnsMapper->mapForm($request->request->get('columns'), $headers)
                , $options);

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

    protected function processRead(Upload $upload, $mappedData, $options)
    {
        $this->getConfig($upload)
            ->processRead($upload, array(
                    'header_mapping' => $mappedData,
                ) + $options);
    }
}
