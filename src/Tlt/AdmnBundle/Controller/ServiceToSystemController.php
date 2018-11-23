<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\FilterDS;
use Tlt\AdmnBundle\Entity\ServiceToSystem;

use Tlt\AdmnBundle\Form\Type\FilterDSType;
use Tlt\AdmnBundle\Form\Type\ServiceToSystemType;

class ServiceToSystemController extends Controller
{
	/**
     * @Route("/sts/index", name="admin_sts_index")
     * @Template("TltAdmnBundle:ServiceToSystem:index.html.twig")
     */
    public function indexAction(Request $request)
    {
		$department = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Department')
					->findOneBy(array());
					
		$filterDS	=	new FilterDS();
		$filterDS->setDepartment($department);
		
		$form	=	$this->createForm(
						FilterDSType::class,
						$filterDS,
						array(
							'method'	=>	'GET'
						)
					);

		$form->handleRequest($request);
		
		$servicesToSystems = null;
		
		if ($form->isValid()) {
			$servicesToSystems = $this->getDoctrine()
						->getRepository('TltAdmnBundle:ServiceToSystem')
						->findBy(
							array(
								'service'	=>	$filterDS->getService()->getId()
							),
							array(
								'service'	=>	'ASC',
								'system'	=>	'ASC'
							)
						);
		}
		
        return $this->render(
						'TltAdmnBundle:ServiceToSystem:index.html.twig',
						array(
							'form'		=>	$form->createView(),
							'servicesToSystems' => $servicesToSystems
						)
					);
    }
	
	/**
     * @Route("/sts/add", name="admin_sts_add")
     * @Template("TltAdmnBundle:ServiceToSystem:add.html.twig")
     */
	public function addAction(Request $request)
	{
		$service = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Service')
					->findOneBy(array());
					
		$serviceToSystem = new ServiceToSystem();
		$serviceToSystem->setService($service);
		$form = $this->createForm( ServiceToSystemType::class, $serviceToSystem);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();
			$serviceToSystem->setInsertedBy($user->getUsername());
			$serviceToSystem->setModifiedBy($user->getUsername());
			$serviceToSystem->setFromHost($this->container->get('request')->getClientIp());

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($serviceToSystem);
			$em->flush();
			
			return $this->redirect($this->generateUrl('admin_sts_success', array('action'=>'add')));
		}
		
		return $this->render('TltAdmnBundle:ServiceToSystem:add.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	/**
     * @Route("/sts/delete/{id}", name="admin_sts_delete")
     */
	public function deleteAction(Request $request, $id)
	{
		$serviceToSystem = $this->getDoctrine()
			->getRepository('TltAdmnBundle:ServiceToSystem')
				->find($id);
		
		// remove object
		$em = $this->getDoctrine()->getManager();
		$em->remove($serviceToSystem);
		$em->flush();
			
		return $this->redirect(
					$this->generateUrl(
						'admin_sts_success',
						array(
							'action'	=>	'delete'
						)
					)
				);
	}
	
	/**
     * @Route("/sts/success/{action}", name="admin_sts_success")
     * @Template("TltAdmnBundle:ServiceToSystem:success.html.twig")
     */
	public function successAction($action)
	{
		return $this->render('TltAdmnBundle:ServiceToSystem:success.html.twig', array('action'=>$action));
	}
}
