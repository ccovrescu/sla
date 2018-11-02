<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Service;
use Tlt\AdmnBundle\Form\Type\ServiceType;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

class ServiceController extends Controller
{
    public function indexAction(Request $request)
    {
		$form = $this->createForm(
			new ChooseType($this->getDoctrine()),
			new Choose(),
			array(
				'department' => array(
					'available'=>true,
					'showAll' => true
				),
			)
		);
		
		$form->handleRequest($request);
		
		$services = null;
		
		if ($form->isValid()) {
			if ($form['department']->getData()!=0) {
				$services = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Service')
					->findAllFromOneDepartmentOrderedByName($form['department']->getData());
			} else {
				$services = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Service')
					->findAllOrderedByName();
			}
		}

        return $this->render(
						'TltAdmnBundle:Service:index.html.twig',
						array(
							'form' => $form->createView(),
							'services' => $services
						)
					);
    }
	
	public function addAction(Request $request)
	{
		$service = new Service();
		$form = $this->createForm( new ServiceType(), $service);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($service);
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_services_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Service:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $id)
	{
		$service = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Service')
			->find($id);
		
		$form = $this->createForm( new ServiceType(), $service);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_services_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Service:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Service:success.html.twig', array('action'=>$action));
	}
}
