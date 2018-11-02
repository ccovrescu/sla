<?php

namespace Tlt\AdmnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Mapping;
use Tlt\AdmnBundle\Form\Type\MappingType;

use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Form\Type\ChooseType;

class MappingController extends Controller
{
    public function indexAction(Request $request, $equipment_id)
    {
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
			->findOneById($equipment_id);
			
		$mappings = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Mapping')
			->findByEquipment($equipment);
		
        return $this->render(
						'TltAdmnBundle:Mapping:index.html.twig',
						array(
							'mappings' => $mappings,
							'equipment' => $equipment
						)
					);
    }
	
	public function addAction(Request $request, $equipment_id)
	{
		$equipment = $this->getDoctrine()
								->getRepository('TltAdmnBundle:Equipment')
									->findOneById($equipment_id);
									
		$mapping = new Mapping();
		$mapping->setEquipment($equipment);

		$form = $this->createForm(
			new MappingType(),
			$mapping,
			array(
				'equipment' => $equipment
			)
		);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($mapping);
			$em->flush();
			
			return $this->redirect(
						$this->generateUrl(
							'tlt_admn_mappings_success',
							array(
								'action'=>'add',
								'mapping_id'=>$mapping->getId()
							)
						)
					);
		}
		
		return $this->render('TltAdmnBundle:Mapping:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function editAction(Request $request, $mapping_id)
	{
		$mapping = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Mapping')
			->find($mapping_id);
		
		$form = $this->createForm(
					new MappingType(),
					$mapping,
					array(
						'equipment' => $mapping->getEquipment()
					)
				);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->flush();
			
			return $this->redirect(
						$this->generateUrl(
							'tlt_admn_mappings_success',
							array(
								'action'=>'edit',
								'mapping_id'=>$mapping_id
							)
						)
					);
		}
		
		return $this->render('TltAdmnBundle:Mapping:edit.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function deleteAction(Request $request, $mapping_id)
	{
		$mapping = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Mapping')
				->find($mapping_id);
				
		$equipment_id = $mapping->getEquipment()->getId();
		
		// remove object
		$em = $this->getDoctrine()->getManager();
		$em->remove($mapping);
		$em->flush();
			
		return $this->redirect(
					$this->generateUrl(
						'tlt_admn_mappings_homepage',
						array(
							'equipment_id'	=>	$equipment_id
						)
					)
				);
	}
	
	public function successAction($action, $mapping_id)
	{
		$mapping = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Mapping')
			->findOneById($mapping_id);
			
		return $this->render(
			'TltAdmnBundle:Mapping:success.html.twig',
			array(
				'action'	=>	$action,
				'mapping'	=>	$mapping
			)
		);
	}
}
