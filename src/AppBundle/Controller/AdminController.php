<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction(Request $request)
    {
        return $this->render('admin/admin.html.twig');
    }

    /**
     * @Route("/admin/upload", name="admin_upload")
     */
    public function uploadAction(Request $request)
    {


        return $this->render('admin/upload.html.twig');
    }
}
