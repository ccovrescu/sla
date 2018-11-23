<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Entity\Mapping;
use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Form\Type\MappingType;

class MappingController extends Controller
{
	/**
     * @Route("/mappings/index/{equipment_id}", name="admin_mappings_index")
     * @Template("TltAdmnBundle:Mappings:index.html.twig")
     */
    public function indexAction(Request $request, $equipment_id)
    {
		$equipment = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Equipment')
			->findOneById($equipment_id);
			
		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
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
		else
			return $this->redirect($this->generateUrl('denied'));
	}
	
	/**
     * @Route("/mappings/add/{equipment_id}", name="admin_mappings_add")
     * @Template("TltAdmnBundle:Mapping:add.html.twig")
     */
	public function addAction(Request $request, $equipment_id)
	{
		$equipment = $this->getDoctrine()
								->getRepository('TltAdmnBundle:Equipment')
									->findOneById($equipment_id);
									
		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$mapping = new Mapping();
			$mapping->setEquipment($equipment);

			$form = $this->createForm(
				MappingType::class,
				$mapping,
				array(
					'equipment' => $equipment
				)
			);

			$form->handleRequest($request);


			if ($form->isValid()) {

			    //introdus 11.10.2018

//                var_dump($mapping->getSystem()->getId());
//                var_dump($mapping->getSystem()->getName());
                //die();

//                if ($mapping->getSystem()->getId() == 161 or $mapping->getSystem()->getId() == 162 or $mapping->getSystem()->getId() == 163 ) {
                    if ( substr($mapping->getSystem()->getName(),0,29)=="Sistem implicit import echip." ) {
                    $form->get('system')->addError(
                    new FormError('Sistemele de IMPORT din vechea aplicatie NU pot fi folosite in mapari!')
                    );
//                    echo "<script>alert('Sistemele de IMPORT din vechea aplicatie NU pot fi folosite in mapari!');</script>";
                     $templateOptions['form'] = $form->createView();

                     return $templateOptions;
                }
                // sf introdus 11.10.2018
				$user	=	$this->getUser();
				$mapping->setInsertedBy($user->getUsername());
				$mapping->setModifiedBy($user->getUsername());
				$mapping->setFromHost($this->container->get('request')->getClientIp());

				// perform some action, such as saving the task to the database
				$em = $this->getDoctrine()->getManager();
				$em->persist($mapping);
				$em->flush();
				
				return $this->redirect(
							$this->generateUrl(
								'admin_mappings_success',
								array(
									'action'=>'add',
									'mapping_id'=>$mapping->getId()
								)
							)
						);
			}

			// introdus 11.10.2018
            $templateOptions['form'] = $form->createView();

            return $templateOptions;
            // sf introdus 11.10.2018
/*			return $this->render('TltAdmnBundle:Mapping:add.html.twig', array(
				'form' => $form->createView(),
			));
*/
    	}
		else
			return $this->redirect($this->generateUrl('denied'));
	}
	
	/**
     * @Route("/mappings/edit/{mapping_id}", name="admin_mappings_edit")
     * @Template("TltAdmnBundle:Mapping:edit.html.twig")
     */
	public function editAction(Request $request, $mapping_id)
	{
		$mapping = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Mapping')
			->find($mapping_id);
		
		if (in_array($mapping->getEquipment()->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($mapping->getEquipment()->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$form = $this->createForm(
						MappingType::class,
						$mapping,
						array(
							'equipment' => $mapping->getEquipment()
						)
					);
			
			$form->handleRequest($request);
			
			if ($form->isValid()) {

                //introdus 11.10.2018

//                var_dump($mapping->getSystem()->getId());
//                var_dump($mapping->getSystem()->getName());
                //die();

//                if ($mapping->getSystem()->getId() == 161 or $mapping->getSystem()->getId() == 162 or $mapping->getSystem()->getId() == 163 ) {
                if ( substr($mapping->getSystem()->getName(),0,29)=="Sistem implicit import echip." ) {
                    $form->get('system')->addError(
                        new FormError('Sistemele de IMPORT din vechea aplicatie NU pot fi folosite in mapari!')
                    );
//                    echo "<script>alert('Sistemele de IMPORT din vechea aplicatie NU pot fi folosite in mapari!');</script>";
                    $templateOptions['form'] = $form->createView();

                    return $templateOptions;
                }
                // sf introdus 11.10.2018


                $user	=	$this->getUser();
				$mapping->setModifiedBy($user->getUsername());
				$mapping->setFromHost($this->container->get('request')->getClientIp());

				// perform some action, such as saving the task to the database
				$em = $this->getDoctrine()->getManager();
				$em->flush();
				
				return $this->redirect(
							$this->generateUrl(
								'admin_mappings_success',
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
		else
			return $this->redirect($this->generateUrl('denied'));
	}
	
	/**
     * @Route("/mappings/delete/{mapping_id}", name="admin_mappings_delete")
     * @Template("TltAdmnBundle:Mappings:delete.html.twig")
     */
	public function deleteAction(Request $request, $mapping_id)
	{
		$mapping = $this->getDoctrine()
			->getRepository('TltAdmnBundle:Mapping')
				->find($mapping_id);
				
		if (in_array($mapping->getEquipment()->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($mapping->getEquipment()->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$equipment_id = $mapping->getEquipment()->getId();
			
			// remove object
			$em = $this->getDoctrine()->getManager();
			$em->remove($mapping);
			$em->flush();
				
			return $this->redirect(
						$this->generateUrl(
							'admin_mappings_index',
							array(
								'equipment_id'	=>	$equipment_id
							)
						)
					);
    	}
		else
			return $this->redirect($this->generateUrl('denied'));
	}
	
	/**
     * @Route("/mappings/success/{action}/{mapping_id}", name="admin_mappings_success")
     * @Template("TltAdmnBundle:Mappings:success.html.twig")
     */
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
