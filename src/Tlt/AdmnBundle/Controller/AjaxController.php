<?php

namespace Tlt\AdmnBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

class AjaxController extends Controller
{
    /**
     * @Route("/fhs7/announcers", name="admin_ajax_announcers")
     */
    public function announcersAction(Request $request)
    {
        $words = explode(" ", $request->get('q'));
        $branch = $request->get('branch');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:Announcer')
                    ->createQueryBuilder('a')
                    ->select(array('a.id, a.firstname, a.lastname, a.compartment, b.name, CONCAT (a.lastname,\' \', a.firstname) as fullname'))
                    ->leftJoin('a.branch', 'b')
                    ->orderBy('a.firstname, a.lastname, b.name, a.compartment');



        foreach ($words as $word) {
            $qb = $qb->orWhere(
                $qb->expr()->andX(
                    $qb->expr()->like('a.firstname', $qb->expr()->literal('%' . $word . '%'))
                )
            );

            $qb = $qb->orWhere(
                $qb->expr()->like('a.lastname', $qb->expr()->literal('%' . $word . '%'))
            );
        }

        $qb = $qb->andWhere('a.branch=:branch')
                    ->setParameter('branch', $branch);

        $qb = $qb->andWhere('a.active=1');

        $results = [
            'items' => $qb->getQuery()->getResult()
        ];

        return new JsonResponse($results);
    }

    /**
     * @Route("/fhs7/equipments", name="admin_ajax_equipments")
     */
    public function eqAction(Request $request)
    {
        $query = str_replace(" ", "%", $request->get('q'));

        $rsm = new ResultSetMapping();

        $em = $this->getDoctrine()->getManager();
        $sqlQuery =
            "SELECT
              eq.id AS id,
              eq.name AS name,
              sv.name AS service,
              lo.name AS location,
              ifnull(group_concat(pr.value),'') as properties
            FROM
              equipments eq
            LEFT JOIN services sv
              ON sv.id = eq.service
            LEFT JOIN zones_locations zl
              ON zl.id = eq.zoneLocation
            LEFT JOIN locations lo
              ON lo.id = zl.location_id
            LEFT JOIN properties_values pr
              ON pr.equipment_id = eq.id
            WHERE
                eq.is_active = 1
                AND zl.branch_id IN (" . implode(',',$this->getUser()->getBranchesIds()) . ")
                AND sv.department IN (" . implode(',',$this->getUser()->getDepartmentsIds()) .")
            GROUP BY eq.id HAVING CONCAT(sv.name,lo.name,eq.name,properties) LIKE :query
            ORDER BY
              sv.name,
              lo.name,
              eq.name";

        $params = array(
            'query' => '%' . $query . '%'
        );

        $stmt = $em->getConnection()->prepare($sqlQuery);
        $stmt->execute($params);

        $results = [
            'items' => $stmt->fetchAll()
        ];

        return new JsonResponse($results);
    }


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
//        echo "<script>alert('select_services');</script>";
		$department_id = $request->request->get('department_id');
//        echo "<script>alert($department_id);</script>";

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

//        echo "<script>alert($department_id);</script>";
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

//        $em = $this->getDoctrine()->getManager();
//        $qb = $em->getRepository('TltAdmnBundle:Equipment')
//            ->createQueryBuilder('eq')
//            ->select('eq.name')
//            ->where('eq.id = :id')
//            ->setParameter('id', $equipment_id);
//
//
//        $equipment	= $qb->getQuery()->getResult();

        $rsm = new ResultSetMapping();

        $em = $this->getDoctrine()->getManager();

       $sqlQuery = "
          SELECT
              eq.id AS id,
              eq.name AS `name`,
              sv.name AS service,
              lo.name AS location,
              IFNULL(GROUP_CONCAT(pr.value),'') AS properties FROM equipments eq
          LEFT JOIN services sv
              ON sv.id = eq.service
          LEFT JOIN zones_locations zl
              ON zl.id = eq.zoneLocation
          LEFT JOIN locations lo
              ON lo.id = zl.location_id
          LEFT JOIN properties_values pr
              ON pr.equipment_id = eq.id
          WHERE
              eq.id=:id";

        $params = array('id' => $equipment_id);

        $stmt = $em->getConnection()->prepare($sqlQuery);
        $stmt->execute($params);

        $results = $stmt->fetchAll();

        return new JsonResponse($results);
    }

    /*introdusa 09.08.2018 */

    /**
     * @Route("/ajax/allowed-systemsv1", name="get_allowed_systemsv1")
     */
    public function getAllowedSystemsActionvclau(Request $request)
    {
        $equipment_id = $request->request->get('equipment_id');
        echo "<script>alert($equipment_id);</script>";

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:Mapping')
            ->createQueryBuilder('mp')
            ->select('mp.id, s.name, ttm.totalaffected')
            ->leftJoin('mp.ticketMapping', 'ttm')
            ->innerJoin('mp.system', 's')
            ->where('mp.equipment = :equipment')
            ->setParameter('equipment', $equipment_id)
            ->orderBy('s.name', 'ASC');
        //echo $qb->getDQL();
        $mappings	= $qb->getQuery()->getResult();
        return new JsonResponse($mappings);
    }

    /* sf introdusa 09.08.2018 */

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

    /**
     * @Route("/ajax/system-select-category", name="system_select_category")
     */
    public function systemCategoryAction(Request $request)
    {
        $category_id = $request->request->get('category_id');
        $department_id	=	$request->request->get('department_id');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:SystemCategory')
            ->createQueryBuilder('categ')
            ->select('categ.id, categ.name')
            ->where('categ.department = :department')
            ->setParameter('department', $department_id)
            ->orderBy('categ.name', 'ASC');


        $categories	= $qb->getQuery()->getResult();

        return new JsonResponse($categories);
    }

    /**
     * @Route("/ajax/equipment-select-system3", name="equipment_select_system3")
     */

    public function equipmentselectsystemAction3(Request $request)
    {

        $service_id = $request->request->get('service_id');

        $repository =$this->getDoctrine()->getRepository('TltAdmnBundle:Service');

        $query=$repository->createQueryBuilder('s')
            ->select('d.id')
            ->join('s.department', 'd')
            ->where('s.id = :service_id')
            ->setParameter('service_id', $service_id)
            ->getQuery();

        $dep_id=$query->getScalarResult();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('TltAdmnBundle:System')
            ->createQueryBuilder('system')
            ->select('system.id, system.name')
            ->leftJoin('system.category','sc')
            ->where('sc.department=:department_id')
            ->setParameter('department_id',$dep_id);

        $systems	= $qb->getQuery()->getResult();

        return new JsonResponse($systems);
    }
    /**
     * @Route("/ajax/equipment-select-system", name="equipment_select_system")
     */

    public function equipmentselectsystemAction(Request $request)
    {

        $service_id = $request->request->get('service_id');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:ServiceToSystem')
            ->createQueryBuilder('ss')
            ->select('system.id, system.name')
            ->join('ss.system','system')
            ->where('ss.service=:service_id')
            ->setParameter('service_id',$service_id);

        $systems	= $qb->getQuery()->getResult();

        return new JsonResponse($systems);
    }


    /**
     * @Route("/ajax/equipment-select-system1", name="equipment_select_system1")
     */

    public function equipmentselectsystemAction1(Request $request)
    {


        $service_id = $request->request->get('service_id');

        $repository =$this->getDoctrine()->getRepository('TltAdmnBundle:Service');

        $query=$repository->createQueryBuilder('s')
            ->select('d.id')
            ->join('s.department', 'd')
            ->where('s.id = :service_id')
            ->setParameter('service_id', $service_id)
            ->getQuery();

        $dep_id=$query->getScalarResult();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:System')
            ->createQueryBuilder('system')
            ->select('system.id, system.name')
            ->join('system.department','d')
            ->where('d.id=:department_id')
            ->setParameter('department_id',$dep_id);


        $systems	= $qb->getQuery()->getResult();



        return new JsonResponse($systems);
    }

    // filter_select_systems

    /**
     * @Route("/ajax/filter-systems", name="filter_select_systems")
     */

    public function filterselectsystemsAction(Request $request)
    {

        $department_id = $request->request->get('department_id');

        $service_id		= $request->request->get('service_id');

/*        echo "<script>alert($service_id);</script>";

        echo "<script>alert($department_id);</script>";
*/

//        echo "<script>alert('same message');</script>";

// cu selectare din mapari Servicii - Sisteme

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:ServiceToSystem')
            ->createQueryBuilder('ss')
            ->select('distinct system.id, system.name')
            ->join('ss.system','system');

        if ($service_id) {
            $qb->andWhere('ss.service = :service_id')
                ->setParameter('service_id', $service_id);
        } elseif ($department_id) {
            $qb->andWhere('system.department = :department')
                ->setParameter('department', $department_id);
        }

/*    $qb=$em->getRepository('TltAdmnBundle:System')
        ->createQueryBuilder('sys')
        ->select('sys.id, sys.name')
        ->where('sys.department = :department')
        ->setParameter('department', $department_id);
*/
        $systems	= $qb->getQuery()->getResult();

        return new JsonResponse($systems);
    }

//    introdus 24.10.2018
    /**
     * @Route("/ajax/pam-filter-systems", name="pam_select_systems")
     */

    public function pamselectsystemsAction(Request $request)
    {
        $owner_id = $request->request->get('owner_id');
        $department_id = $request->request->get('department_id');

        $service_id		= $request->request->get('service_id');

        /*        echo "<script>alert($service_id);</script>";

                echo "<script>alert($department_id);</script>";
        */

//        echo "<script>alert('same message');</script>";

// cu selectare din mapari Servicii - Sisteme

        $em = $this->getDoctrine()->getManager();
/*        $qb = $em->getRepository('TltAdmnBundle:ServiceToSystem')
            ->createQueryBuilder('ss')
            ->select('distinct system.id, system.name')
            ->join('ss.system','system');
*/

        $qb = $em->getRepository('TltAdmnBundle:System')
            ->createQueryBuilder('sys')
            ->select('distinct sys.id, sys.name')
            ->innerJoin('sys.equipments', 'e')
            ->innerJoin('e.service', 'srv')
            ->andWhere('e.isActive=true')
            ->andWhere('e.inPam=true')
            ->andWhere('e.owner = :owner_id')
            ->setParameter('owner_id', $owner_id)
            ->orderBy('sys.name', 'ASC');

/*            ->select('distinct sys.id, sys.name')
            ->innerJoin('sys.mappings', 'mp')
            ->innerJoin('mp.equipment', 'e')
            ->innerJoin('e.service', 'srv')
            ->andWhere('e.isActive=true')
            ->andWhere('e.owner = :owner_id')
            ->setParameter('owner_id', $owner_id)
            ->orderBy('sys.name', 'ASC');
*/
        if ($service_id) {
            $qb->andWhere('e.service = :service_id')
                ->setParameter('service_id', $service_id);
        } elseif ($department_id) {
            $qb->andWhere('sys.department = :department')
                ->setParameter('department', $department_id);
        }
/*
 					->select('distinct system.id, system.name')
					->leftJoin('system.mappings', 'mapping')
                    ->where('mapping.equipment = :equipment')
					->orderBy('system.name', 'ASC')
 */

//        $qb->orderBy('sys', 'ASC');

        /*    $qb=$em->getRepository('TltAdmnBundle:System')
                ->createQueryBuilder('sys')
                ->select('sys.id, sys.name')
                ->where('sys.department = :department')
                ->setParameter('department', $department_id);
        */
        $systems	= $qb->getQuery()->getResult();

        return new JsonResponse($systems);
    }
//    sfarsit introdus 24.10.2018

}