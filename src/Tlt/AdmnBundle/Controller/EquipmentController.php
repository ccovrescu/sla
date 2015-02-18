<?php
namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Tlt\AdmnBundle\Entity\Equipment;
use Tlt\AdmnBundle\Form\Type\EquipmentType;

use Tlt\AdmnBundle\Entity\Filter;
use Tlt\AdmnBundle\Form\Type\FilterType;

class EquipmentController extends Controller
{
	/**
     * @Route("/equipments/index", name="admin_equipments_index")
     * @Template("TltAdmnBundle:Equipment:index.html.twig")
     */
    public function indexAction(Request $request)
    {
		$department = $this->getDoctrine()
									->getRepository('TltAdmnBundle:Department')
									->findById(1);
									
		$service = $this->getDoctrine()
									->getRepository('TltAdmnBundle:Service')
									->findById(1);
		
		$zoneLocation = $this->getDoctrine()
									->getRepository('TltAdmnBundle:ZoneLocation')
									->findById(107);
		
		$filter	=	new Filter();
		// $filter->setDepartment($department);
		// $filter->setService($service);
		// $filter->setZoneLocation($zoneLocation);
		
		$form	=	$this->createForm(
						new FilterType( $this->getUser() ),
						$filter,
						array(
							'zone'			=>	true,
							'zoneLocation'	=>	true,
							'department'	=>	true,
							'service'		=>	true,
							'owner'			=>	true,
							'method'		=>	'GET',
						)
					);
		
		$form->handleRequest($request);
		
		$equipments = null;
		if ($form->isValid()) {
			$equipments = $this->getDoctrine()
									->getRepository('TltAdmnBundle:Equipment')
										->findAllJoinedToBranchesAndServices(
											( $filter->getOwner() ? $filter->getOwner()->getId() : null),
											( $filter->getBranch() ? $filter->getBranch()->getId() : null),
											( $filter->getZoneLocation() ? $filter->getZoneLocation()->getId() : null),
											( $filter->getDepartment() ? $filter->getDepartment()->getId() : null),
											( $filter->getService() ? $filter->getService()->getId() : null),
											$this->getUser()->getBranchesIds(),
											$this->getUser()->getDepartmentsIds()
											// $form['owner']->getData(),
											// $form['branch']->getData(),
											// $form['location']->getData(),
											// $form['department']->getData(),
											// $form['service']->getData()
										);
		}
		
        return array(
			'form'		=>	$form->createView(),
			'equipments'	=>	$equipments
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
		
		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$form = $this->createForm(
				new EquipmentType($this->getUser()->getBranchesIds(), $this->getUser()->getDepartmentsIds()),
				$equipment
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