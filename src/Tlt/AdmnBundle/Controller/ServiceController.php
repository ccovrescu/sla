<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Entity\Service;

use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Form\Type\ServiceType;

class ServiceController extends Controller
{
	/**
     * @Route("/services/index", name="admin_services_index")
     * @Template("TltAdmnBundle:Service:index.html.twig")
     */
    public function indexAction(Request $request)
    {
		$form = $this->createForm(
			ChooseType::class,
			new Choose(),
			array(
				'department' => array(
					'available'=>true,
					'showAll' => true),
                    'doctrine' => $this->getDoctrine(),
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
	
	/**
     * @Route("/services/add", name="admin_services_add")
     * @Template("TltAdmnBundle:Service:add.html.twig")
     */
	public function addAction(Request $request)
	{
		$service = new Service();
		$form = $this->createForm(  ServiceType::class, $service);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$service->setInsertedBy($user->getUsername());
			$service->setModifiedBy($user->getUsername());
			$service->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($service);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_services_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Service:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/services/edit/{id}", name="admin_services_edit")
     * @Template("TltAdmnBundle:Service:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$service = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Service')
			->find($id);
		
		$form = $this->createForm(  ServiceType::class, $service);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$service->setModifiedBy($user->getUsername());
			$service->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_services_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Service:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/services/success/{action}", name="admin_services_success")
     * @Template("TltAdmnBundle:Service:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Service:success.html.twig', array('action'=>$action));
	}
}
