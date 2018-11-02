<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Form\Type\BranchType;


class BranchController extends Controller
{
    public function indexAction()
    {
		$branches = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Branch')
			->findAll();
			
        return $this->render('TltAdmnBundle:Branch:index.html.twig', array('branches' => $branches));
    }
	
	public function addAction(Request $request)
	{
		$branch = new Branch();
		$form = $this->createForm( new BranchType(), $branch);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($branch);
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_branch_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Branch:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $id)
	{
		$branch = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Branch')
			->find($id);
		
		$form = $this->createForm( new BranchType(), $branch);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_branch_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Branch:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Branch:success.html.twig', array('action'=>$action));
	}
}