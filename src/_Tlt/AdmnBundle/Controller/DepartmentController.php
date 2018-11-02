<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Department;
use Tlt\AdmnBundle\Form\Type\DepartmentType;

class DepartmentController extends Controller
{
    public function indexAction(Request $req)
    {
		$departments = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Department')
			->findAll();
			
        return $this->render('TltAdmnBundle:Department:index.html.twig', array('departments' => $departments));
    }
	
	public function addAction(Request $request)
	{
		$dep = new Department();
		$form = $this->createForm( new DepartmentType(), $dep);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($dep);
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_departments_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Department:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $id)
	{
		$dep = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Department')
			->find($id);
		
		$form = $this->createForm( new DepartmentType(), $dep);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_departments_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Department:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Department:success.html.twig', array('action'=>$action));
	}
}
