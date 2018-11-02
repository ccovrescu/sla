<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
						->findBy(
							array('branch'	=>	$branch),
							array('name'	=>	'ASC')
						);
						// ->findByBranch(
							// $em->getRepository(
								// 'TltAdmnBundle:Branch'
							// )
							// ->findById(
								// $branch
							// ));
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
						->findBy(
							array('department'	=>	$department),
							array('name'	=>	'ASC')
						);						
						// ->findByDepartment(
							// $em->getRepository(
								// 'TltAdmnBundle:Department'
							// )
							// ->findById(
								// $department
							// ));
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
	 * @Route("/ajax/services2", name="select_services")
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
	
	/**
	 * @Route("/aj/systems", name="select_aj_systems")
	 */
	public function systemsAction(Request $request)
	{
		$service_id = $request->request->get('service_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:System')
					->createQueryBuilder('sys')
					->select('distinct sys.id, sys.name')
					->innerJoin('sys.department', 'd')
					->innerJoin('d.services', 'sv')
					->where('sv.id = :service')
					->setParameter('service', $service_id)
					->orderBy('sys.name', 'ASC');
					
		$systems	= $qb->getQuery()->getResult();
		
		return new JsonResponse($systems);
	}

	/**
	 * @Route("/ajax/filter-locations", name="filter_select_locations")
	 */
	public function filterLocationsAction(Request $request)
	{
		$branch_id = $request->request->get('branch_id');
		$service_id	=	$request->request->get('service_id');
		$department_id	=	$request->request->get('department_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:ZoneLocation')
					->createQueryBuilder('zl')
					->select(array('zl.id', 'l.name'))
					->distinct()
					->innerJoin('zl.location', 'l')
					->innerJoin('zl.equipments', 'eq')
                    ->where('zl.branch = :branch')
					->andWhere('eq.isActive = :isActive')
                    ->setParameter('branch', $branch_id)
					->setParameter('isActive', true)
					->orderBy('l.name', 'ASC');
					
		if ($service_id) {
			$qb->andWhere('eq.service = :service')
				->setParameter('service', $service_id);
		} elseif ($department_id) {
			$qb->innerJoin('eq.service', 'sv')
				->andWhere('sv.department = :department')
				->setParameter('department', $department_id);
		}
		
		$locations	= $qb->getQuery()->getResult();
		
		return new JsonResponse($locations);
	}
	
	/**
	 * @Route("/ajax/filter-services", name="filter_select_services")
	 */
	public function filterServicesAction(Request $request)
	{
		$department_id = $request->request->get('department_id');
 
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Service')
					->createQueryBuilder('sv')
					->select('distinct sv.id, sv.name')
					
					->innerJoin('sv.equipments', 'eq')
					->innerJoin('eq.zoneLocation', 'zl')
					->where('eq.isActive = :isActive')
					->andWhere('zl.branch IN (:userBranches)')
					->andWhere('sv.department IN (:userDepartments)')
					->setParameter('isActive', true)
					->setParameter('userBranches', $this->getUser()->getBranchesIds())
					->setParameter('userDepartments', $this->getUser()->getDepartmentsIds())
					->orderby('sv.name', 'ASC');
					
		if ($department_id)
			$qb->andWhere('sv.department = :department')
				->setParameter('department', $department_id);
		
		$services	= $qb->getQuery()->getResult();
		
		return new JsonResponse($services);
	}
	
	// /**
	 // * @Route("/filter-owners-by-branch", name="filter_select_owners_by_branch")
	 // */
	// public function filterOwnersByBranchAction(Request $request)
	// {
		// $branch_id = $request->request->get('branch_id');
 
		// $em = $this->getDoctrine()->getManager();
		// $qb = $em->getRepository('TltAdmnBundle:Owner')
					// ->createQueryBuilder('owner')
					// ->select('distinct owner.id, owner.name')
                    // ->innerJoin('owner.equipments', 'equipment')
					// ->innerJoin('equipment.zoneLocation', 'zl')
                    // ->where('zl.branch = :branch')
                    // ->setParameter('branch', $branch_id)
					// ->orderby('owner.name', 'ASC');
		
		// $services	= $qb->getQuery()->getResult();
		
		// return new JsonResponse($services);
	// }
	
	/**
	 * @Route("/ajax/filter-owners", name="filter_select_owners")
	 */
	public function filterOwnersAction(Request $request)
	{
		$department_id	= $request->request->get('department_id');
		$service_id		= $request->request->get('service_id');
		$branch_id		= $request->request->get('branch_id');
		$location_id	= $request->request->get('location_id');
		
		$userBranches		=	$this->getUser()->getBranchesIds();
		$userDepartments	=	$this->getUser()->getDepartmentsIds();
		
		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Owner')
					->createQueryBuilder('ow')
					->select('distinct ow.id, ow.name')
					
                    ->innerJoin('ow.equipments', 'eq')
					->innerJoin('eq.zoneLocation', 'zl')
					->innerJoin('eq.service', 'sv')
					->where('eq.isActive = :isActive')
					->andWhere('zl.branch IN (:userBranches)')
					->andWhere('sv.department IN (:userDepartments)')
					->setParameter('isActive', true)
					->setParameter('userBranches', $userBranches)
					->setParameter('userDepartments', $userDepartments)
					->orderby('ow.name', 'ASC');
					
		if ($location_id) {
			$qb->andWhere('eq.zoneLocation = :zoneLocation')
				->setParameter('zoneLocation', $location_id);
		} elseif ($branch_id) {
			$qb->andWhere('zl.branch = :branch')
				->setParameter('branch', $branch_id);
		}
		
		if ($service_id) {
			$qb->andWhere('eq.service = :service')
				->setParameter('service', $service_id);
		} elseif ($department_id) {
			$qb->andWhere('sv.department = :department')
				->setParameter('department', $department_id);
		}
		
		$owners	= $qb->getQuery()->getResult();
		
		return new JsonResponse($owners);
	}
	
	/**
	 * @Route("/ajax/filter-branches", name="filter_select_branches")
	 */
	public function filterBranchesAction(Request $request)
	{
		$department_id = $request->request->get('department_id');
		$service_id = $request->request->get('service_id');
 
		$userBranches		=	$this->getUser()->getBranchesIds();
		$userDepartments	=	$this->getUser()->getDepartmentsIds();

		$em = $this->getDoctrine()->getManager();
		$qb = $em->getRepository('TltAdmnBundle:Branch')
				->createQueryBuilder('br')
				->select('distinct br.id, br.name')
				
				->innerJoin('br.zoneLocations', 'zl')
				->innerJoin('zl.equipments', 'eq')
				->innerJoin('eq.service', 'sv')
				->where('eq.isActive = :isActive')
				->andWhere('br.id IN (:userBranches)')
				->andWhere('sv.department IN (:userDepartments)')
				->setParameter('isActive', true)
				->setParameter('userBranches', $userBranches)
				->setParameter('userDepartments', $userDepartments)
				->orderBy('br.name', 'ASC');
		
				if ($service_id)
				{
					$qb->andWhere('eq.service = :service')
						->setParameter('service', $service_id);
				} elseif ($department_id)
				{
					$qb->andWhere('sv.department = :department')
						->setParameter('department', $department_id);
				}
		
		$owners	= $qb->getQuery()->getResult();
		
		return new JsonResponse($owners);
	}

    /**
     * @Route("/ajax/filter-equipments", name="filter_select_equipments")
     */
    public function filterEquipmentsAction(Request $request)
    {
        $department_id = $request->request->get('department_id');
        $service_id = $request->request->get('service_id');
        $branch_id = $request->request->get('branch_id');
        $zoneLocation_id = $request->request->get('location_id');
        $owner_id = $request->request->get('owner_id');

        $userBranches		=	$this->getUser()->getBranchesIds();
        $userDepartments	=	$this->getUser()->getDepartmentsIds();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:Equipment')
            ->createQueryBuilder('eq')
            ->select('distinct eq.id, eq.name')
//            ->select('distinct eq.id, CONCAT(eq.name, Group_Concat(pv.value SEPARATOR \'|\')) as name')

            ->innerJoin('eq.zoneLocation', 'zl')
            ->innerJoin('eq.service', 'sv')
//            ->innerJoin('eq.propertiesValues', 'pv')
            ->where('eq.isActive = :isActive')
            ->andWhere('zl.branch IN (:userBranches)')
            ->andWhere('sv.department IN (:userDepartments)')
            ->setParameter('isActive', true)
            ->setParameter('userBranches', $userBranches)
            ->setParameter('userDepartments', $userDepartments)
            ->groupBy('eq.id')
            ->orderBy('eq.name', 'ASC');

        if ($owner_id) {
            $qb->andWhere('eq.owner = :owner')
                ->setParameter('owner', $owner_id);
        }

        if ($zoneLocation_id) {
            $qb->andWhere('eq.zoneLocation = :zoneLocation')
                ->setParameter('zoneLocation', $zoneLocation_id);
        } elseif ($branch_id) {
            $qb->andWhere('zl.branch = :branch')
                ->setParameter('branch', $branch_id);
        }

        if ($service_id) {
            $qb->andWhere('eq.service = :service')
                ->setParameter('service', $service_id);
        } elseif ($department_id) {
            $qb->andWhere('sv.department = :department')
                ->setParameter('department', $department_id);
        }

        $equipments	= $qb->getQuery()->getResult();

        return new JsonResponse($equipments);
    }

	// /**
	 // * @Route("/filter-branches-by-service", name="filter_select_branches_by_service")
	 // */
	// public function filterBranchesByServiceAction(Request $request)
	// {
		// $service_id = $request->request->get('service_id');
 
		// $em = $this->getDoctrine()->getManager();
		// $qb = $em->getRepository('TltAdmnBundle:Service')
					// ->createQueryBuilder('s')
					// ->select('distinct b.id, b.name')
					// ->innerJoin('s.equipments', 'e')
					// ->innerJoin('e.zoneLocation', 'zl')
					// ->innerJoin('zl.branch', 'b')
                    // ->where('s.id = :service')
					// ->andWhere('b.id IN (:userBranches)')
                    // ->setParameter('service', $service_id)
					// ->setParameter('userBranches', $this->getUser()->getBranchesIds())
					// ->orderby('b.name', 'ASC');
		
		// $owners	= $qb->getQuery()->getResult();
		
		// return new JsonResponse($owners);
	// }

    /**
     * @Route("/ajax/equipment-name", name="get_equipment_name")
     */
    public function getEquipmentNameAction(Request $request)
    {
        $equipment_id = $request->request->get('equipment_id');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:Equipment')
            ->createQueryBuilder('eq')
            ->select('eq.name')
            ->where('eq.id = :id')
            ->setParameter('id', $equipment_id);


        $equipment	= $qb->getQuery()->getResult();

        return new JsonResponse($equipment);
    }

    /**
     * @Route("/ajax/allowed-systems", name="get_allowed_systems")
     */
    public function getAllowedSystemsAction(Request $request)
    {
        $equipment_id = $request->request->get('equipment_id');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:Mapping')
            ->createQueryBuilder('mp')
            ->select('mp.id, s.name')
            ->innerJoin('mp.system', 's')
            ->where('mp.equipment = :equipment')
            ->setParameter('equipment', $equipment_id)
            ->orderBy('s.name', 'ASC');

        $mappings	= $qb->getQuery()->getResult();

        return new JsonResponse($mappings);
    }
}