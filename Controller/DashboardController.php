<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function indexAction()
    {
        $types = $this->container->getParameter('upload_data.upload_types');

        return $this->render('@UploadData/Dashboard/index.html.twig', array(
            'types' => $types,
        ));
    }
}
