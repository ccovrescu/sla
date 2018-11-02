<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
	/**
     * @Route("/index", name="admin_index")
     * @Template("TltOwnerBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        return $this->render('TltAdmnBundle:Default:index.html.twig');
    }
}
