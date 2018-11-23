<?php
namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Owner;
use Tlt\AdmnBundle\Form\Type\OwnerType;


class OwnerController extends Controller
{
	/**
     * @Route("/owners/index", name="admin_owners_index")
     * @Template("TltAdmnBundle:Owner:index.html.twig")
     */
    public function indexAction()
    {
		$owners = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Owner')
			->findAll();
			
        return $this->render('TltAdmnBundle:Owner:index.html.twig', array('owners' => $owners));
    }
	
	/**
     * @Route("/owners/add", name="admin_owners_add")
     * @Template("TltAdmnBundle:Owner:add.html.twig")
     */
	
	public function addAction(Request $request)
	{
		//echo "<script>alert('alert 2');</script>";
		$owner = new Owner();
		$form = $this->createForm( 'Tlt\AdmnBundle\Form\Type\OwnerType', $owner);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$owner->setInsertedBy($user->getUsername());
			$owner->setModifiedBy($user->getUsername());
			$owner->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($owner);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_owners_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Owner:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/owners/edit/{id}", name="admin_owners_edit")
     * @Template("TltAdmnBundle:Owner:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$owner = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Owner')
			->find($id);
		
		$form = $this->createForm( 'Tlt\AdmnBundle\Form\Type\OwnerType', $owner);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$owner->setModifiedBy($user->getUsername());
			$owner->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_owners_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Owner:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/owners/success/{action}", name="admin_owners_success")
     * @Template("TltAdmnBundle:Owner:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Owner:success.html.twig', array('action'=>$action));
	}
}