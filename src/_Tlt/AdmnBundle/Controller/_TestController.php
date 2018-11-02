<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tlt\AdmnBundle\Form\Type\ChooseEquipmentType;
use Tlt\AdmnBundle\Entity\Equipment;

class TestController extends Controller
{
/**
 * @Route("/agencies_centers", name="select_provinces")
 */
public function agenciesCentersAction(Request $request)
{
    $country_id = $request->request->get('country_id');
 
    $em = $this->getDoctrine()->getManager();
    $provinces = $em->getRepository('MainBundle:Province')->findByCountryId($country_id);
 
    return new JsonResponse($provinces);
}
 
/**
 * @Route("/locationsx", name="select_cities")
 */
public function locationsAction(Request $request)
{
    $agency_center = $request->get('agency_center_id');
 
    $em = $this->getDoctrine()->getManager();
	
	$locations = array(array(0, 'Toate'));
	$results = $em->getRepository(
						'TltAdmnBundle:Location'
					)
					->findByAgencyCenter(
						$em->getRepository(
							'TltAdmnBundle:AgencyCenter'
						)
						->findById(
							$agency_center
						));
	foreach ($results as $location)
	{
		$locations[] = array($location->getId(), $location->getName());
	}
 
    return new JsonResponse($locations);
}

public function servicesAction(Request $request)
{
    $department = $request->get('department_id');
 
    $em = $this->getDoctrine()->getManager();
	
	$services = array(array(0, 'Toate'));
	$results = $em->getRepository(
						'TltAdmnBundle:Service'
					)
					->findByDepartment(
						$em->getRepository(
							'TltAdmnBundle:Department'
						)
						->findById(
							$department
						));
	foreach ($results as $service)
	{
		$services[] = array($service->getId(), $service->getName());
	}
 
    return new JsonResponse($services);
}

/**
 * @Route("/test")
 */

	public function indexAction(Request $request)
	{
		$form = $this->createForm( new ChooseEquipmentType(), new Equipment());
		
		$form->handleRequest($request);
		if ($form->isValid()) {
			// var_dump( $form->getData() );
			// die();
			// $form = $this->createForm( new ChooseEquipmentType(), $form->getData() );
		} else {
			die('not valid');
		}
		
		return $this->render('TltAdmnBundle:Test:index.html.twig', array(
			'form' => $form->createView(),
		));
	}
}