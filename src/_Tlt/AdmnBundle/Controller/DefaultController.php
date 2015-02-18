<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('TltAdmnBundle:Default:index.html.twig');
    }
}
