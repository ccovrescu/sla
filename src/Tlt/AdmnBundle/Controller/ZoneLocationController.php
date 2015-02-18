<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Tlt\AdmnBundle\Entity\ZoneLocation;
use Tlt\AdmnBundle\Form\Type\ZoneLocationType;

use Tlt\AdmnBundle\Entity\Filter;
use Tlt\AdmnBundle\Form\Type\FilterType;

class ZoneLocationController extends Controller
{
	/**
     * @Route("/zone-locations/index", name="admin_zone_locations_index")
     * @Template("TltAdmnBundle:ZoneLocation:index.html.twig")
     */
    public function indexAction(Request $request)
    {
		$filter = new Filter();
		$form = $this->createForm(
			new FilterType($this->getUser()),
			$filter,
			array(
				'zone'	=> true
			)
		);
		
		$form->handleRequest($request);
		
		$zoneLocations = null;
		
		if ($form->isValid()) 
		{
			if ($form['branch']->getData() instanceof \Tlt\AdmnBundle\Entity\Branch) {
				$zoneLocations = $this->getDoctrine()
					->getRepository('TltAdmnBundle:ZoneLocation')
					->findByBranch($form['branch']->getData());
			} else {
				$zoneLocations = $this->getDoctrine()
					->getRepository('TltAdmnBundle:ZoneLocation')
					->findByBranch( $this->getUser()->getBranchesIds() );
			}
		}
			
        return $this->render('TltAdmnBundle:ZoneLocation:index.html.twig', array(
			'form' => $form->createView(),
			'zoneLocations' => $zoneLocations
		));
    }

	/**
     * @Route("/zone-locations/add", name="admin_zone_locations_add")
     * @Template("TltAdmnBundle:ZoneLocation:add.html.twig")
     */
	public function addAction(Request $request)
	{
		$zoneLocation = new ZoneLocation();
		$form = $this->createForm( new ZoneLocationType($this->getUser()), $zoneLocation);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			
			$zoneLocation->setInsertedBy($user->getUsername());
			$zoneLocation->setModifiedBy($user->getUsername());
			$zoneLocation->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($zoneLocation);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_zone_locations_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:ZoneLocation:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/zone-locations/edit/{id}", name="admin_zone_locations_edit")
     * @Template("TltAdmnBundle:ZoneLocation:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$zoneLocation = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ZoneLocation')
			->find($id);
		
		if (in_array($zoneLocation->getBranch()->getId(), $this->getUser()->getBranchesIds()))
		{
			$form = $this->createForm( new ZoneLocationType($this->getUser()), $zoneLocation);
			
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$user	=	$this->getUser();
				$zoneLocation->setModifiedBy($user->getUsername());
				$zoneLocation->setFromHost($this->container->get('request')->getClientIp());

				// perform some action, such as saving the task to the database
				$em = $this->getDoctrine()->getManager();
				$em->flush();
				
				return $this->redirect($this->generateUrl('admin_zone_locations_success', array('action'=>'edit')));
			}
			
			return $this->render('TltAdmnBundle:ZoneLocation:edit.html.twig', array(
				'form' => $form->createView(),
			));
		}
		else
			return $this->redirect($this->generateUrl('denied'));
	}

	/**
     * @Route("/zone-locations/delete/{id}", name="admin_zone_locations_delete")
     */
	public function deleteAction(Request $request, $id)
	{
		$zoneLocation = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ZoneLocation')
				->find($id);
				
		if (in_array($zoneLocation->getBranch()->getId(), $this->getUser()->getBranchesIds()))
		{
			// remove object
			$em = $this->getDoctrine()->getManager();
			$em->remove($zoneLocation);
			$em->flush();
				
			return $this->redirect(
						$this->generateUrl(
							'admin_zone_locations_index'
						)
					);
		}
		else
			return $this->redirect($this->generateUrl('denied'));
	}

	/**
     * @Route("/zone-locations/success/{action}", name="admin_zone_locations_success")
     * @Template("TltAdmnBundle:ZoneLocation:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:ZoneLocation:success.html.twig', array('action'=>$action));
	}
}