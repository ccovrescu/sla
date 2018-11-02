<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\System;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Form\Type\SystemType;

class SystemController extends Controller
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
		
		$systems = null;
		
		if ($form->isValid()) {
			if ($form['department']->getData()!=0) {
				$systems = $this->getDoctrine()
					->getRepository('TltAdmnBundle:System')
					->findAllFromOneDepartmentOrderedByName($form['department']->getData());
			} else {
				$systems = $this->getDoctrine()
					->getRepository('TltAdmnBundle:System')
					->findAllOrderedByName();
			}
		}

        return $this->render(
						'TltAdmnBundle:System:index.html.twig',
						array(
							'form' => $form->createView(),
							'systems' => $systems
						)
					);
    }

	public function addAction(Request $request)
	{
		$system = new System();
		$form = $this->createForm( new SystemType(), $system);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($system);
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_systems_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:System:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $id)
	{
		$system = $this->getDoctrine()
			->getRepository('TltAdmnBundle:System')
			->find($id);
		
		$form = $this->createForm( new SystemType(), $system);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_systems_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:System:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:System:success.html.twig', array('action'=>$action));
	}
}
