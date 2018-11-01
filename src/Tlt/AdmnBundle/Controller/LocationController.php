<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Entity\Location;

use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Form\Type\LocationType;

class LocationController extends Controller
{
	/**
     * @Route("/locations/index", name="admin_locations_index")
     * @Template("TltAdmnBundle:Location:index.html.twig")
     */
    public function indexAction(Request $request)
    {
		$locations = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Location')
			->findBy(array(), array('name'=>'asc'));
			
        return $this->render('TltAdmnBundle:Location:index.html.twig', array(
			// 'form' => $form->createView(),
			'locations' => $locations
		));
    }
	
	/**
     * @Route("/locations/add", name="admin_locations_add")
     * @Template("TltAdmnBundle:Location:add.html.twig")
     */
	public function addAction(Request $request)
	{
		$location = new Location();
		$form = $this->createForm( new LocationType(), $location);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$location->setInsertedBy($user->getUsername());
			$location->setModifiedBy($user->getUsername());
			$location->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($location);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_locations_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Location:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/locations/edit/{id}", name="admin_locations_edit")
     * @Template("TltAdmnBundle:Location:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$location = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Location')
			->find($id);
		
		$form = $this->createForm( new LocationType(), $location);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$location->setModifiedBy($user->getUsername());
			$location->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_locations_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Location:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/locations/success/{action}", name="admin_locations_success")
     * @Template("TltAdmnBundle:Location:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Location:success.html.twig', array('action'=>$action));
	}
}