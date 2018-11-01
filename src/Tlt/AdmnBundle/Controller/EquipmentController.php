<?php
namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Tlt\AdmnBundle\Entity\Equipment;
use Tlt\AdmnBundle\Entity\Filter;

use Tlt\AdmnBundle\Form\Type\EquipmentType;
use Tlt\AdmnBundle\Form\Type\FilterType;

class EquipmentController extends Controller
{
	/**
     * @Route("/equipments/index", name="admin_equipments_index")
     * @Template("TltAdmnBundle:Equipment:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $session = $this->get('session');
        // Check if data already was submitted and validated
        if ($session->has('submittedData')) {
            $filter = $session->get('submittedData');
        } else {
            $filter = new Filter();
        }
		$form	=	$this->createForm(
						new FilterType($this->getDoctrine()->getManager(), $this->getUser() ),
						$filter,
						array(
							'zone'			=>	true,
							'zoneLocation'	=>	true,
							'department'	=>	true,
							'service'		=>	true,
							'system'        =>  true,
							'owner'			=>	true,
							'method'		=>	'GET',
						)
					);

		$form->handleRequest($request);
		$equipments = array();
		if ($form->isValid()) {
            // Data is valid so save it in session for another request
            $session->set('submittedData', $form->getData());
            if ($request->query->get('limit') != null)
                $session->set('limit', $request->query->get('limit', 10));

			$equipments = $this->getDoctrine()
									->getRepository('TltAdmnBundle:Equipment')
										->findAllJoinedToBranchesAndServices(
											( $filter->getOwner() ? $filter->getOwner()->getId() : null),
											( $filter->getBranch() ? $filter->getBranch()->getId() : null),
											( $filter->getZoneLocation() ? $filter->getZoneLocation()->getId() : null),
											( $filter->getDepartment() ? $filter->getDepartment()->getId() : null),
											( $filter->getService() ? $filter->getService()->getId() : null),
                                            ( $filter->getSystem() ? $filter->getSystem()->getId() : null),
											$this->getUser()->getBranchesIds(),
											$this->getUser()->getDepartmentsIds()
										);
		}

        $paginator  = $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(array('limit' => $session->get('limit', $request->query->get('limit', 10))));
        $pagination = $paginator->paginate(
            $equipments,
            $request->query->get('page', 1)/*page number*/,
            $session->get('limit', $request->query->get('limit', 10))/*limit per page*/
        );

		
        return array(
			'form'		=>	$form->createView(),
			'pagination'	=>	$pagination,
		);
    }
	
	public function xindexAction(Request $request)
    {
			
		$tokenStorage = $this->container->get('security.context');
		
		// $form = $this->createForm( new ChooseEquipmentType(), new Equipment());
		$form = $this->createForm(
			new ChooseType(/*$tokenStorage,*/ $this->getDoctrine()),
			new Choose(),
			array(
				'owner' => array(
					'available'=>true,
					'showAll' => true
				),
				'branch' => array(
					'available'=>true,
					'showAll' => true
				),
				'location' => array(
					'available'=>true,
					'showAll' => true
				),
				'department' => array(
					'available'=>true,
					'showAll' => true
				),
				'service' => array(
					'available'=>true,
					'showAll' => true
				)
			)
		);
		
		$form->handleRequest($request);
		
		$equipments = null;
		
		if ($form->isValid()) {
			$equipments = $this->getDoctrine()
									->getRepository('TltAdmnBundle:Equipment')
										->findAllJoinedToBranchesAndServices(
											$form['owner']->getData(),
											$form['branch']->getData(),
											$form['location']->getData(),
											$form['department']->getData(),
											$form['service']->getData()
										);
		}
		
        return $this->render('TltAdmnBundle:Equipment:index.html.twig', array(
			'form' => $form->createView(),
			'equipments' => $equipments
		));
    }
	
	/**
     * @Route("/equipments/add", name="admin_equipments_add")
     * @Template("TltAdmnBundle:Equipment:add.html.twig")
     */
	public function addAction(Request $request)
	{
		if ($this->getUser()->getBranchesIds() == null)
			throw new NotFoundHttpException("Lipseste asocierea user:branch");
		if ($this->getUser()->getDepartmentsIds() == null)
			throw new NotFoundHttpException("Lipseste asocierea user:department");

		
		$equipment = new Equipment();
		// $form = $this->createForm( new EquipmentType(), $equipment);
		
		$form = $this->createForm(
			new EquipmentType($this->getUser()->getBranchesIds(), $this->getUser()->getDepartmentsIds()),
			$equipment
		);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$equipment->setInsertedBy($user->getUsername());
			$equipment->setModifiedBy($user->getUsername());
			$equipment->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($equipment);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_equipments_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Equipment:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/equipments/edit/{id}", name="admin_equipments_edit")
     * @Template("TltAdmnBundle:Equipment:index.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		if ($this->getUser()->getBranchesIds() == null)
			throw new NotFoundHttpException("Lipseste asocierea user:branch");
		if ($this->getUser()->getDepartmentsIds() == null)
			throw new NotFoundHttpException("Lipseste asocierea user:department");
		
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
			->find($id);

// introdus pe 19.07.2018
  $service_id = $equipment->getService();

 /*       $repository =$this->getDoctrine()->getRepository('TltAdmnBundle:Service');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('TltAdmnBundle:ServiceToSystem')
            ->createQueryBuilder('ss')
            ->select('system.id, system.name')
            ->join('ss.system','system')
            ->where('ss.service=:service_id')
            ->setParameter('service_id',$service_id);

        $systems	= $qb->getQuery()->getResult();


*/

// introdus pe 19.07.2018

        $systems=$equipment->getSystem();
//        die($equipment->getSystem());

		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$form = $this->createForm(
				new EquipmentType($this->getUser()->getBranchesIds(), $this->getUser()->getDepartmentsIds()),
				$equipment, array(
                    'systems'=>$equipment->getSystem(),
                )
			);
			
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$user	=	$this->getUser();
				$equipment->setModifiedBy($user->getUsername());
				$equipment->setFromHost($this->container->get('request')->getClientIp());

				// perform some action, such as saving the task to the database
				$em = $this->getDoctrine()->getManager();
				$em->flush();
				
				return $this->redirect($this->generateUrl('admin_equipments_success', array('action'=>'edit')));
			}
				
			
			return $this->render('TltAdmnBundle:Equipment:edit.html.twig', array(
                    'form' => $form->createView(),
                    'equipment' => $equipment
			));
		}
		else
			return $this->redirect($this->generateUrl('denied'));
	}
	
	/**
     * @Route("/equipments/details/{id}", name="admin_equipments_details")
     * @Template("TltAdmnBundle:Equipment:details.html.twig")
     */
	public function detailsAction(Request $request, $id)
	{
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
			->find($id);
		
		return $this->render('TltAdmnBundle:Equipment:details.html.twig', array(
			'equipment' => $equipment
		));
	}
	
	/**
     * @Route("/equipments/success/{action}", name="admin_equipments_success")
     * @Template("TltAdmnBundle:Equipment:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Equipment:success.html.twig', array('action'=>$action));
	}
	
	/**
     * @Route("/equipments/delete/{equipment_id}", name="admin_equipments_delete")
     * @Template("TltAdmnBundle:Equipments:delete.html.twig")
     */
	public function deleteAction(Request $request, $equipment_id)
	{
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
				->find($equipment_id);
		
		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$equipment->setIsActive( false );
			
			// remove object
			$em = $this->getDoctrine()->getManager();
			$em->flush();
				
			return $this->redirect(
						$this->generateUrl(
							'admin_equipments_success',
							array(
								'action'	=>	'delete'
							)
						)
					);
		}
		else
			return $this->redirect($this->generateUrl('denied'));
	}
}