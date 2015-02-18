<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Owner;
use Tlt\AdmnBundle\Form\Type\OwnerType;


class OwnerController extends Controller
{
    public function indexAction()
    {
		$owners = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Owner')
			->findAll();
			
        return $this->render('TltAdmnBundle:Owner:index.html.twig', array('owners' => $owners));
    }
	
	public function addAction(Request $request)
	{
		$owner = new Owner();
		$form = $this->createForm( new OwnerType(), $owner);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($owner);
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_owners_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Owner:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $id)
	{
		$owner = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Owner')
			->find($id);
		
		$form = $this->createForm( new OwnerType(), $owner);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('tlt_admn_owners_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Owner:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Owner:success.html.twig', array('action'=>$action));
	}
}