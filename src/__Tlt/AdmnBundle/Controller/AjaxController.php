<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController extends Controller
{
	/**
     * @Route("/fhs7/locations/{branch_id}/{all}", defaults={"all" = 0}, name="admin_ajax_locations")
     * @Template("TltAdmnBundle:Branch:index.html.twig")
     */
	public function locationsAction(Request $request)
	{
		$branch = $request->get('branch_id');
		$all = $request->get('all');
 
		$em = $this->getDoctrine()->getManager();
	
		if ($all==1)
			$locations = array(array(0, 'Toate'));
		else
			$locations = array();
			
		$results = $em->getRepository(
							'TltAdmnBundle:Location'
						)
						->findByBranch(
							$em->getRepository(
								'TltAdmnBundle:Branch'
							)
							->findById(
								$branch
							));
		foreach ($results as $location)
		{
			$locations[] = array($location->getId(), $location->getName());
		}
 
		return new JsonResponse($locations);
	}

	/**
     * @Route("/fhs7/services/{department_id}/{all}", defaults={"all" = 0}, name="admin_ajax_services")
     * @Template("TltAdmnBundle:Branch:index.html.twig")
     */
	public function servicesAction(Request $request)
	{
		$department = $request->get('department_id');
		$all = $request->get('all');
 
		$em = $this->getDoctrine()->getManager();
	
		if ($all==1)
			$services = array(array(0, 'Toate'));
		else
			$services = array();
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
	
	public function equipmentsAction(Request $request, $location_id, $service_id, $all)
	{
		// $location = $request->get('location_id');
		// $service = $request->get('service_id');
		// $all = $request->get('all');
		 
		$em = $this->getDoctrine()->getManager();
	
		if ($all==1)
			$equipments = array(array(0, 'Toate'));
		else
			$equipments = array();
			
		$results = $em->getRepository(
							'TltAdmnBundle:Equipment'
						)
						->myFindBy(0, $location_id, 0, $service_id);
		
		foreach ($results as $equipment)
		{
			// $equipments[] = array($equipment->getId(), $equipment->getName());
			$equipments[] = array($equipment->getId(), $equipment->getUniqueName());
		}
 
		return new JsonResponse($equipments);
		// return new JsonResponse(
			// array(
				// '1'=>$location_id,
				// '2'=>$service_id
			// )
		// )
	}
	
	/**
	 * @Route("/branches", name="select_branches")
	 */
	public function branchesAction(Request $request)
	{
		$owner_id = $request->request->get('owner_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Branch')
					->createQueryBuilder('branch')
					->select('distinct branch.id, branch.name')
                    ->innerJoin('branch.locations', 'location')
					->innerJoin('location.equipments', 'equipment')
                    ->where('equipment.owner = :owner')
                    ->setParameter('owner', $owner_id);
		
		$branches	= $qb->getQuery()->getResult();
		
		return new JsonResponse($branches);
	}

	/**
	 * @Route("/locations2", name="select_locations")
	 */
	public function locations2Action(Request $request)
	{
		$branch_id = $request->request->get('branch_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Location')
					->createQueryBuilder('location')
					->select('distinct location.id, location.name')
                    ->where('location.branch = :branch')
                    ->setParameter('branch', $branch_id);
		
		$locations	= $qb->getQuery()->getResult();
		
		return new JsonResponse($locations);
	}

	/**
	 * @Route("/services2", name="select_services")
	 */
	public function services2Action(Request $request)
	{
		$department_id = $request->request->get('department_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Service')
					->createQueryBuilder('service')
					->select('distinct service.id, service.name')
                    ->where('service.department = :department')
                    ->setParameter('department', $department_id);
		
		$services	= $qb->getQuery()->getResult();
		
		return new JsonResponse($services);
	}
	
	/**
	 * @Route("/equipments2", name="select_equipments")
	 */
	public function equipments2Action(Request $request/*, $location_id, $service_id*/)
	{
		$location_id = $request->request->get('location_id');
		$service_id = $request->request->get('service_id');
		
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Equipment')
					->createQueryBuilder('equipment')
					->select('distinct equipment.id, equipment.name');
					
		if ($location_id)
			$qb = $qb
                    ->where('equipment.location = :location')
                    ->setParameter('location', $location_id);
		if ($service_id)
			$qb = $qb
                    ->andWhere('equipment.service = :service')
                    ->setParameter('service', $service_id);
					
		$equipments	= $qb->getQuery()->getResult();
		
		return new JsonResponse($equipments);
	}
	/**
	 * @Route("/systems2", name="select_systems")
	 */
	public function systems2Action(Request $request)
	{
		$equipment_id = $request->request->get('equipment_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:System')
					->createQueryBuilder('system')
					->select('distinct system.id, system.name')
					->leftJoin('system.mappings', 'mapping')
                    ->where('mapping.equipment = :equipment')
					->orderBy('system.name', 'ASC')
                    ->setParameter('equipment', $equipment_id);
		
		$systems	= $qb->getQuery()->getResult();
		
		return new JsonResponse($systems);
	}
}