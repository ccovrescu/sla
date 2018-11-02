<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\ServiceAttribute;
use Tlt\AdmnBundle\Form\Type\ServiceAttributeType;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

class ServiceAttributeController extends Controller
{
	/**
     * @Route("/properties/index", name="admin_properties_index")
     * @Template("TltAdmnBundle:ServiceAttribute:index.html.twig")
     */
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
				'service' => array(
					'available'=>true,
					'showAll' => true
				),
			)
		);
		
		$form->handleRequest($request);
		
		$serviceAttribute_attributes = null;
		
		if ($form->isValid()) {
			if ($form['service']->getData()!=0) {
				$serviceAttribute_attributes = $this->getDoctrine()
					->getRepository('TltAdmnBundle:ServiceAttribute')
					->findByService($form['service']->getData());
			} else {
				$serviceAttribute_attributes = $this->getDoctrine()
					->getRepository('TltAdmnBundle:ServiceAttribute')
					->findAll();
			}
		}

        return $this->render(
						'TltAdmnBundle:ServiceAttribute:index.html.twig',
						array(
							'form' => $form->createView(),
							'services' => $serviceAttribute_attributes
						)
					);
    }
	
	/**
     * @Route("/properties/add", name="admin_properties_add")
     * @Template("TltAdmnBundle:ServiceAttribute:index.html.twig")
     */
	public function addAction(Request $request)
	{
		$serviceAttribute = new ServiceAttribute();
		$form = $this->createForm( new ServiceAttributeType(), $serviceAttribute);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceAttribute->setInsertedBy($user->getUsername());
			$serviceAttribute->setModifiedBy($user->getUsername());
			$serviceAttribute->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($serviceAttribute);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_properties_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:ServiceAttribute:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/properties/edit/{id}", name="admin_properties_edit")
     * @Template("TltAdmnBundle:ServiceAttribute:index.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$serviceAttribute = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ServiceAttribute')
			->find($id);
		
		$form = $this->createForm( new ServiceAttributeType(), $serviceAttribute);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceAttribute->setModifiedBy($user->getUsername());
			$serviceAttribute->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_properties_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:ServiceAttribute:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/properties/success/{action}", name="admin_properties_success")
     * @Template("TltAdmnBundle:ServiceAttribute:index.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:ServiceAttribute:success.html.twig', array('action'=>$action));
	}
}
