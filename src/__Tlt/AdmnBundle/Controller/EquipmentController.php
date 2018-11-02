<?php
namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Equipment;
use Tlt\AdmnBundle\Form\Type\EquipmentType;
// use Tlt\AdmnBundle\Form\Type\ChooseEquipmentType;
use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Entity\Choose;

class EquipmentController extends Controller
{
	/**
     * @Route("/equipments/index", name="admin_equipments_index")
     * @Template("TltAdmnBundle:Equipment:index.html.twig")
     */
	public function indexAction(Request $request)
    {
		// $form = $this->createForm( new ChooseEquipmentType(), new Equipment());
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
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
		$equipment = new Equipment();
		$form = $this->createForm( new EquipmentType(), $equipment);
		
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
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
			->find($id);
		
		$form = $this->createForm( new EquipmentType(), $equipment);
		
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
}