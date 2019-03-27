<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Department;
use Tlt\AdmnBundle\Form\Type\DepartmentType;

class DepartmentController extends Controller
{
	/**
     * @Route("/departments/index", name="admin_departments_index")
     * @Template("TltAdmnBundle:Department:index.html.twig")
     */
    public function indexAction(Request $req)
    {
		$departments = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Department')
			->findAll();
			
        return $this->render('TltAdmnBundle:Department:index.html.twig', array('departments' => $departments));
    }
	
	/**
     * @Route("/departments/add", name="admin_departments_add")
     * @Template("TltAdmnBundle:Department:add.html.twig")
     */
	public function addAction(Request $request)
	{
		$dep = new Department();
		$form = $this->createForm( DepartmentType::class, $dep);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$dep->setInsertedBy($user->getUsername());
			$dep->setModifiedBy($user->getUsername());
			$dep->setFromHost($request->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($dep);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_departments_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Department:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/departments/edit/{id}", name="admin_departments_edit")
     * @Template("TltAdmnBundle:Department:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$dep = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Department')
			->find($id);
		
		$form = $this->createForm( DepartmentType::class, $dep);

		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$dep->setModifiedBy($user->getUsername());
			$dep->setFromHost($request->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_departments_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Department:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/departments/success/{action}", name="admin_departments_success")
     * @Template("TltAdmnBundle:Department:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Department:success.html.twig', array('action'=>$action));
	}
}
