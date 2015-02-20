<?php
namespace Tlt\TicketBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Acl\Exception\Exception;
use Tlt\TicketBundle\Entity\Ticket;
use Tlt\TicketBundle\Entity\TicketCreate;
use Tlt\TicketBundle\Entity\TicketAllocation;
use Tlt\TicketBundle\Entity\TicketEquipment;
use Tlt\TicketBundle\Entity\TicketSystem;
use Tlt\TicketBundle\Entity\TicketFix;
use Tlt\TicketBundle\Form\Type\TicketCreateType;
use Tlt\TicketBundle\Form\Type\TicketReallocateType;
use Tlt\TicketBundle\Form\Type\TicketEquipmentType;
use Tlt\TicketBundle\Form\Type\TicketSystemType;
use Tlt\TicketBundle\Form\Type\TicketFixType;

class DefaultController extends Controller
{
    /**
     * @Route("/tickets", name="tickets")
     * @Template()
     */
    public function indexAction()
    {
		$tickets = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketCreate')
			->findTicketsByBranches($this->getUser()->getBranchesIds());


		return $this->render(
			'TltTicketBundle:Default:index.html.twig',
			array(
				'tickets' => $tickets
			));
	}

    /**
     * @Route("/add_ticket", name="add_ticket")
     * @Template()
     */
    public function addAction(Request $request)
    {
		$ticketCreate = new TicketCreate();
		$form = $this->createForm( new TicketCreateType($this->getUser()), $ticketCreate);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$user	=	$this->getUser();

			$ticketCreate->setInsertedBy($user->getUsername());
			$ticketCreate->setModifiedBy($user->getUsername());
			$ticketCreate->setFromHost($this->container->get('request')->getClientIp());

            $ticketCreate->updateTicketAllocation();
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($ticketCreate);
			$em->flush();
			
			return $this->redirect(
				$this->generateUrl(
					'success_ticket',
					array(
						'action'	=>	'add',
						'id'		=>	$ticketCreate->getId()
					)
				)
			);
		}
		
		return $this->render('TltTicketBundle:Default:add.html.twig', array(
			'form' => $form->createView(),
		));		
	}
	
    /**
     * @Route("/reallocate_ticket/{id}", name="reallocate_ticket")
     * @Template("TltTicketBundle:Default:reallocate.html.twig")
     */
	public function reallocateTicket(Request $request, $id)
	{
		$ticketCreate = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketCreate')
			->findOneById($id);

        $ticketAllocation = new TicketAllocation();
		
		$form = $this->createForm( new TicketReallocateType($this->getUser()), $ticketAllocation );
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
            $user	=	$this->getUser();

            $ticketAllocation->setInsertedBy($user->getUsername());
            $ticketAllocation->setModifiedBy($user->getUsername());
            $ticketAllocation->setFromHost($this->container->get('request')->getClientIp());

            $ticketAllocation->setTicketCreate( $ticketCreate );

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($ticketAllocation);
			$em->flush();
			
			return $this->redirect(
				$this->generateUrl(
					'success_ticket',
					array(
						'action'	=>	'reallocate',
						'id'		=>	$ticketAllocation->getId()
					)
				)
			);
		}
		
		return
			array(
				'ticket' => $ticketCreate,
				'form' => $form->createView()
			);	
	}
	
    /**
     * @Route("/add_equip_to_ticket/{id}", name="add_equip_to_ticket")
     * @Template("TltTicketBundle:Default:add_equip.html.twig")
     */
	public function addEquipmentTicket(Request $request, $id)
	{
        /* Finding first department of current user. */
        $currentDepartment = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Department')
            ->findOneById($this->getUser()->getDepartmentsIds());


        /* Finding the first service of current user. */
        $req = new Request();
        $req->request->set('department_id', $currentDepartment->getId());

        $response = $this->forward('TltAdmnBundle:Ajax:filterServices', array(
            'request'  => $req
        ));

        $currentService = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Service')
            ->findOneById(json_decode($response->getContent())[0]->id);


        /* Finding first zone of current user. */
        $req = new Request();
        $req->request->set('department_id', $currentDepartment->getId());
        $req->request->set('service_id', $currentService->getId());

        $response = $this->forward('TltAdmnBundle:Ajax:filterBranches', array(
            'request'  => $req
        ));

        $currentZone = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Branch')
            ->findOneById(json_decode($response->getContent())[0]->id);


        /* Finding first zoneLocation of current user. */
        $req = new Request();
        $req->request->set('branch_id', $currentZone->getId());
        $req->request->set('service_id', $currentService->getId());

        $response = $this->forward('TltAdmnBundle:Ajax:filterLocations', array(
            'request'  => $req
        ));

        $currentZoneLocation = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:ZoneLocation')
            ->findOneById(json_decode($response->getContent())[0]->id);


        /* Finding first owner of current user. */
        $req = new Request();
        $req->request->set('department_id', $currentDepartment->getId());
        $req->request->set('service_id', $currentService->getId());
        $req->request->set('branch_id', $currentZone->getId());
        $req->request->set('location_id', $currentZoneLocation->getId());

        $response = $this->forward('TltAdmnBundle:Ajax:filterOwners', array(
            'request'  => $req
        ));

        $currentOwner = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Owner')
            ->findOneById(json_decode($response->getContent())[0]->id);



        $ticketCreate = $this->getDoctrine()
            ->getRepository('TltTicketBundle:TicketCreate')
            ->findOneById($id);

		$ticketEquipment =  new TicketEquipment();
		$ticketEquipment->setTicketCreate($ticketCreate);
        $ticketEquipment->setDepartment($currentDepartment);
        $ticketEquipment->setService($currentService);
        $ticketEquipment->setBranch($currentZone);
        $ticketEquipment->setZoneLocation($currentZoneLocation);
        $ticketEquipment->setOwner($currentOwner);

		$form = $this->createForm(
			new TicketEquipmentType($this->getUser()),
			$ticketEquipment,
            array(
                'department'    =>  true,
                'service'  =>  true,
                'zone' => true,
                'zoneLocation' => true,
                'owner' => true,
                'equipment' => true
            )
		);
		
		
		$form->handleRequest($request);
		
		
		if ($form->isValid()) {

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($ticketEquipment);
			$em->flush();


			return $this->redirect(
				$this->generateUrl(
					'success_ticket',
					array(
						'action'	=>	'add_equip',
						'id'		=>	$ticketEquipment->getId()
					)
				)
			);
		}

		return
			array(
				'ticket' => $ticketCreate,
				'form' => $form->createView()
			);	
	}
	
	
	/**
     * @Route("/rem_equip_from_ticket/{id}", name="rem_equip_from_ticket")
     */
	public function remEquipmentFromTicket(Request $request, $id)
	{
		$ticketEquipment = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketEquipment')
			->findOneById($id);
		
		$equipment = $ticketEquipment->getEquipment();
		$parent = $ticketEquipment->getTicketCreate();
		
		$em = $this->getDoctrine()->getManager();
		$em->remove($ticketEquipment);
		$em->flush();
			
		return $this->redirect(
			$this->generateUrl(
				'success_ticket',
				array(
					'action'	=>	'rem_equip',
					'id'		=>	$equipment->getId(),
					'parent'	=>	$parent->getId()
				)
			)
		);
	}
	
	
	
	/**
     * @Route("/add_sys_to_equip/{id}", name="add_sys_to_equip")
     * @Template("TltTicketBundle:Default:add_sys.html.twig")
     */
	public function addSystemToEquipment(Request $request, $id)
	{
		$ticketEquipment = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketEquipment')
			->findOneById($id);
			
		$ticketSystem = new TicketSystem();
		$ticketSystem->setTicketEquipment( $ticketEquipment );
		
		$form = $this->createForm(
			new TicketSystemType($ticketEquipment),
			$ticketSystem
		);
		
		
		$form->handleRequest($request);
		
		
		if ($form->isValid()) {
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($ticketSystem);
			$em->flush();
			
			return $this->redirect(
				$this->generateUrl(
					'success_ticket',
					array(
						'action'	=>'add_sys',
						// 'ticket'	=> $ticketSystem->getTicketEquipment()->getTicketCreate()->getId(),
						// 'equipment'	=> $ticketSystem->getTicketEquipment()->getId(),
						'id'	=> $ticketSystem->getId()
					)
				)
			);
		}
		
		return
			array(
				'ticketEquipment' => $ticketEquipment,
				'form' => $form->createView()
			);
	}
	
	
	/**
     * @Route("/rem_sys_from_equip/{id}", name="rem_sys_from_equip")
     */
	public function remSystemFromEquipment(Request $request, $id)
	{
		$ticketSystem = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketSystem')
			->findOneById($id);
		
		$system = $ticketSystem->getSystem();
		$parent = $ticketSystem->getTicketEquipment();
			
		$em = $this->getDoctrine()->getManager();
		$em->remove($ticketSystem);
		$em->flush();
			
		return $this->redirect(
			$this->generateUrl(
				'success_ticket',
				array(
					'action'	=>'rem_sys',
					'id'		=> $system->getId(),
					'parent'	=> $parent->getId()
				)
			)
		);
	}

    /**
     * @Route("/fix_ticket/{id}", name="fix_ticket")
     * @Template("TltTicketBundle:Default:fix.html.twig")
     */
	public function fixTicket(Request $request, $id)
	{
		$ticketCreate = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketCreate')
			->findOneById($id);
			
		$ticketFix = new TicketFix();
		$ticketFix->setTicketCreate($ticketCreate);
		
		$form = $this->createForm(
							new TicketFixType(),
							// new TicketFix(),
							$ticketFix,
							array(
								'hasSystems'	=>	$ticketFix->hasSystems()
							)
						);
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
			$ticketFix = $form->getData();
			$ticketFix->setTicketCreate( $ticketCreate );
			
			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($ticketFix);
			$em->flush();
			
			return $this->redirect(
				$this->generateUrl(
					'success_ticket',
					array(
						'action'	=>	'fix',
						'id'		=>	$ticketFix->getId()
					)
				)
			);
		}
		
		return
			array(
				'ticket' => $ticketCreate,
				'form' => $form->createView()
			);	
	}
	
    /**
     * @Route("/ticket_details/{id}", name="ticket_details")
     * @Template("TltTicketBundle:Default:details.html.twig")
     */
    public function detailsAction($id)
    {
		$ticket = $this->getDoctrine()
			->getRepository('TltTicketBundle:TicketCreate')
			->findOneById($id);
		
		return
			array(
				'ticket' => $ticket
			);
    }
	
	/**
     * @Route("/success_ticket/{action}/{id}/{parent}", requirements={"id" = "\d+"}, defaults={"parent" = null}, name="success_ticket")
     * @Template()
     */
	public function successAction($action, $id = null, $parent = null)
	{
		$object = $objectParent = null;
		
		switch ($action)
		{
			case 'add':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketCreate')
					->findOneById($id);
				break;
			case 'reallocate':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketAllocation')
					->findOneById($id);				
				break;
			case 'add_equip':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketEquipment')
					->findOneById($id);
				break;
			case 'rem_equip':
				$object = $this->getDoctrine()
					->getRepository('TltAdmnBundle:Equipment')
					->findOneById($id);
					
				$objectParent = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketCreate')
					->findOneById($parent);
				break;
			case 'add_sys':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketSystem')
					->findOneById($id);
				break;
			case 'rem_sys':
				$object = $this->getDoctrine()
					->getRepository('TltAdmnBundle:System')
					->findOneById($id);
					
				$objectParent = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketEquipment')
					->findOneById($parent);
				break;
			case 'fix':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketFix')
					->findOneById($id);
				break;
		}
		
		return $this->render(
			'TltTicketBundle:Default:success.html.twig',
			array(
				'action'	=>	$action,
				'ticket'	=>	$object,
				'parent'	=>	$objectParent
			)
		);
	}
}