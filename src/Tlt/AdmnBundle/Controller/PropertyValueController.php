<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Entity\PropertyValue;

use Tlt\AdmnBundle\Form\Type\ChooseType;
use Tlt\AdmnBundle\Form\Type\PropertyValueType;

class PropertyValueController extends Controller
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
			
		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$propertyValues = $this->getDoctrine()
				->getRepository('TltAdmnBundle:PropertyValue')
				->findByEquipment($equipment);
			
			return $this->render(
							'TltAdmnBundle:PropertyValue:index.html.twig',
							array(
								'propertiesValues' => $propertyValues,
								'equipment' => $equipment
							)
						);
		}
		else
			return $this->redirect($this->generateUrl('denied'));
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
		
		if (in_array($equipment->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($equipment->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$propertyValue = new PropertyValue();
			$propertyValue->setEquipment($equipment);

			$form = $this->createForm(
				PropertyValueType::class,
				$propertyValue,
				array(
					'equipment' => $equipment
				)
			);
			
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$user	=	$this->getUser();
				$propertyValue->setInsertedBy($user->getUsername());
				$propertyValue->setModifiedBy($user->getUsername());
				$propertyValue->setFromHost($this->container->get('request')->getClientIp());
				
				// perform some action, such as saving the task to the database
				$em = $this->getDoctrine()->getManager();
				$em->persist($propertyValue);
				$em->flush();
				
				return $this->redirect(
							$this->generateUrl(
								'admin_sav_success',
								array(
									'action'=>'add',
									'propertyValueId' => $propertyValue->getId()
								)
							)
						);
			}
			
			return $this->render('TltAdmnBundle:PropertyValue:add.html.twig', array(
				'form' => $form->createView(),
			));
		}
		else
			return $this->redirect($this->generateUrl('denied'));
	}

	/**
     * @Route("/sav/edit/{id}", name="admin_sav_edit")
     * @Template("TltAdmnBundle:ServiceAttrValue:edit.html.twig")
     */
	public function editAction(Request $request, $id)
	{
		$propertyValue = $this->getDoctrine()
			->getRepository('TltAdmnBundle:PropertyValue')
			->findOneById($id);
			
		if (in_array($propertyValue->getEquipment()->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds())
			&& in_array($propertyValue->getEquipment()->getService()->getDepartment()->getId(), $this->getUser()->getDepartmentsIds()))
		{
			$form = $this->createForm(
						PropertyValueType::class,
						$propertyValue,
						array(
							'equipment' => $propertyValue->getEquipment()
						)
					);
			
			$form->handleRequest($request);
			
			if ($form->isValid()) {
				$user	=	$this->getUser();
				$propertyValue->setModifiedBy($user->getUsername());
				$propertyValue->setFromHost($this->container->get('request')->getClientIp());
				
				// perform some action, such as saving the task to the database
				$em = $this->getDoctrine()->getManager();
				$em->flush();
				
				return $this->redirect(
							$this->generateUrl(
								'admin_sav_success',
								array(
									'action'=>'edit',
									'propertyValueId' => $id
								)
							)
						);
			}
			
			return $this->render('TltAdmnBundle:PropertyValue:edit.html.twig', array(
				'form' => $form->createView(),
			));
		}
		else
			return $this->redirect($this->generateUrl('denied'));
	}

	/**
     * @Route("/sav/success/{action}/{propertyValueId}", name="admin_sav_success")
     * @Template("TltAdmnBundle:ServiceAttrValue:success.html.twig")
     */
	public function successAction($action, $propertyValueId)
	{
		$propertyValue = $this->getDoctrine()
			->getRepository('TltAdmnBundle:PropertyValue')
			->findOneById($propertyValueId);
			
		return $this->render(
			'TltAdmnBundle:PropertyValue:success.html.twig',
			array(
				'action'			=>	$action,
				'propertyValue'		=>	$propertyValue
			)
		);
	}

    /**
     * @Route("/sav/delete/{id}", name="admin_sav_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $propertyValue = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:PropertyValue')
            ->find($id);

        $equipment = $propertyValue->getEquipment();

        if (in_array($propertyValue->getEquipment()->getZoneLocation()->getBranch()->getId(), $this->getUser()->getBranchesIds()))
        {
            // remove object
            $em = $this->getDoctrine()->getManager();
            $em->remove($propertyValue);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'admin_sav_index',
                    ['equipment_id' => $equipment->getId()]
                )
            );
        }
        else
            return $this->redirect($this->generateUrl('denied'));
    }
}