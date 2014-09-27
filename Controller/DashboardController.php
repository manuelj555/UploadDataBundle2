<?php

namespace Manuelj555\Bundle\UploadDataBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function indexAction()
    {
        return $this->render('@UploadData/Dashboard/index.html.twig', array());
    }
}
