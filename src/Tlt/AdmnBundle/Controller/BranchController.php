<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Form\Type\BranchType;


class BranchController extends Controller
{
	/**
     * @Route("/branches/index", name="admin_branches_index")
     * @Template("TltAdmnBundle:Branch:index.html.twig")
     */
    public function indexAction()
    {
		$branches = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Branch')
			->findAll();
			
        return $this->render('TltAdmnBundle:Branch:index.html.twig', array('branches' => $branches));
    }
	
	/**
     * @Route("/branches/add", name="admin_branches_add")
     * @Template("TltAdmnBundle:Branch:add.html.twig")
     */
	public function addAction(Request $request)
	{
		$branch = new Branch();
		$form = $this->createForm( new BranchType(), $branch);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$branch->setInsertedBy($user->getUsername());
			$branch->setModifiedBy($user->getUsername());
			$branch->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($branch);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_branches_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Branch:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/branches/edit/{id}", name="admin_branches_edit")
     * @Template("TltAdmnBundle:Branch:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$branch = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Branch')
			->find($id);
		
		$form = $this->createForm( new BranchType(), $branch);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$branch->setModifiedBy($user->getUsername());
			$branch->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_branches_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Branch:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/branches/success/{action}", name="admin_branches_success")
     * @Template("TltAdmnBundle:Branch:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Branch:success.html.twig', array('action'=>$action));
	}
}