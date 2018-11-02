<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\ServiceAttributeValue;
use Tlt\AdmnBundle\Form\Type\ServiceAttributeValueType;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

class ServiceAttributeValueController extends Controller
{
	/**
     * @Route("/sav/index/{equipment_id}", name="admin_sav_index")
     * @Template("TltAdmnBundle:ServiceAttrValue:index.html.twig")
     */
    public function indexAction(Request $request, $equipment_id)
    {
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
			->findOneById($equipment_id);
			
		$serviceAttributeValues = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ServiceAttributeValue')
			->findByEquipment($equipment);
		
        return $this->render(
						'TltAdmnBundle:ServiceAttributeValue:index.html.twig',
						array(
							'serviceAttributeValues' => $serviceAttributeValues,
							'equipment' => $equipment
						)
					);
    }
	
	/**
     * @Route("/sav/add/{equipment_id}", name="admin_sav_add")
     * @Template("TltAdmnBundle:ServiceAttrValue:add.html.twig")
     */
	public function addAction(Request $request, $equipment_id)
	{
		$equipment = $this->getDoctrine()
								->getRepository('TltAdmnBundle:Equipment')
									->findOneById($equipment_id);
		
		$serviceAttributeValue = new ServiceAttributeValue();
		$serviceAttributeValue->setEquipment($equipment);

		$form = $this->createForm(
			new ServiceAttributeValueType(),
			$serviceAttributeValue,
			array(
				'equipment' => $equipment
			)
		);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceAttributeValue->setInsertedBy($user->getUsername());
			$serviceAttributeValue->setModifiedBy($user->getUsername());
			$serviceAttributeValue->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($serviceAttributeValue);
			$em->flush();
			
			return $this->redirect(
						$this->generateUrl(
							'admin_sav_success',
							array(
								'action'=>'add',
								'serviceAttributeValueId' => $serviceAttributeValue->getId()
							)
						)
					);
		}
		
		return $this->render('TltAdmnBundle:ServiceAttributeValue:add.html.twig', array(
			'form' => $form->createView(),
		));
	}

	/**
     * @Route("/sav/edit/{id}", name="admin_sav_edit")
     * @Template("TltAdmnBundle:ServiceAttrValue:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$serviceAttributeValue = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ServiceAttributeValue')
			->findOneById($id);
			
		$form = $this->createForm(
					new ServiceAttributeValueType(),
					$serviceAttributeValue,
					array(
						'equipment' => $serviceAttributeValue->getEquipment()
					)
				);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceAttributeValue->setModifiedBy($user->getUsername());
			$serviceAttributeValue->setFromHost($this->container->get('request')->getClientIp());
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect(
						$this->generateUrl(
							'admin_sav_success',
							array(
								'action'=>'edit',
								'serviceAttributeValueId' => $id
							)
						)
					);
		}
		
		return $this->render('TltAdmnBundle:ServiceAttributeValue:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}

	/**
     * @Route("/sav/success/{action}/{serviceAttributeValueId}", name="admin_sav_success")
     * @Template("TltAdmnBundle:ServiceAttrValue:success.html.twig")
     */
	public function successAction($action, $serviceAttributeValueId)
	{
		$serviceAttributeValue = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ServiceAttributeValue')
			->findOneById($serviceAttributeValueId);
			
		return $this->render(
			'TltAdmnBundle:ServiceAttributeValue:success.html.twig',
			array(
				'action'				=>	$action,
				'serviceAttributeValue'	=>	$serviceAttributeValue
			)
		);
	}
}