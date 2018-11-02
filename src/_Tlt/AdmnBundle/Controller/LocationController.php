<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Location;
use Tlt\AdmnBundle\Form\Type\LocationType;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

class LocationController extends Controller
{
    public function indexAction(Request $request)
    {
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'branch' => array(
					'available'=>true,
					'showAll' => true
				),
			)
		);
		
		$form->handleRequest($request);
		
		$locations = null;
		
		if ($form->isValid()) {
			if ($form['branch']->getData()!=0) {
				$locations = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Location')
					// ->findByBranch($form['branch']->getData());
					->findAllFromOneAgencyCenterOrderedByName($form['branch']->getData());
			} else {
				$locations = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Location')
					->findAllOrderedByName();
			}
		}

			
        return $this->render('TltAdmnBundle:Location:index.html.twig', array(
			'form' => $form->createView(),
			'locations' => $locations
		));
    }
	
	public function addAction(Request $request)
	{
		$location = new Location();
		$form = $this->createForm( new LocationType(), $location);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($location);
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_locations_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Location:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $id)
	{
		$location = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Location')
			->find($id);
		
		$form = $this->createForm( new LocationType(), $location);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_locations_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Location:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Location:success.html.twig', array('action'=>$action));
	}
}