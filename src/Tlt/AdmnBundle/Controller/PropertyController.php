<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Entity\Property;

use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Form\Type\PropertyType;

class PropertyController extends Controller
{
	/**
     * @Route("/properties/index", name="admin_properties_index")
     * @Template("TltAdmnBundle:Property:index.html.twig")
     */
    public function indexAction(Request $request)
    {
		$properties = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Property')
					->findAll();

        return $this->render(
						'TltAdmnBundle:Property:index.html.twig',
						array(
							'properties' => $properties
						)
					);
    }
	
	/**
     * @Route("/properties/add", name="admin_properties_add")
     * @Template("TltAdmnBundle:Property:index.html.twig")
     */
	public function addAction(Request $request)
	{
		$serviceAttribute = new Property();
		$form = $this->createForm( PropertyType::class, $serviceAttribute);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceAttribute->setInsertedBy($user->getUsername());
			$serviceAttribute->setModifiedBy($user->getUsername());
			$serviceAttribute->setFromHost($request->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($serviceAttribute);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_properties_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:Property:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/properties/edit/{id}", name="admin_properties_edit")
     * @Template("TltAdmnBundle:Property:index.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$serviceAttribute = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Property')
			->find($id);
		
		$form = $this->createForm( PropertyType::class, $serviceAttribute);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceAttribute->setModifiedBy($user->getUsername());
			$serviceAttribute->setFromHost($request->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_properties_success', array('action'=>'edit')));
		}
		
		return $this->render('TltAdmnBundle:Property:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/properties/success/{action}", name="admin_properties_success")
     * @Template("TltAdmnBundle:Property:index.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:Property:success.html.twig', array('action'=>$action));
	}
}
