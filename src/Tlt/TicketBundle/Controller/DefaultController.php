<?php
namespace Tlt\TicketBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Tlt\TicketBundle\Entity\Ticket;
use Tlt\TicketBundle\Form\Type\Model\TicketFilters;
use Tlt\TicketBundle\Form\Type\TicketFiltersType;
use Tlt\TicketBundle\Form\Type\TicketType;
use Tlt\TicketBundle\Entity\TicketAllocation;
use Tlt\TicketBundle\Entity\TicketEquipment;
use Tlt\TicketBundle\Form\Type\TicketReallocateType;
use Tlt\TicketBundle\Form\Type\TicketEquipmentType;

class DefaultController extends Controller
{
    /**
     * @Route("/tickets", name="tickets")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $session = $this->get('session');

        // Check if filter data already was submitted and validated for this page
        if ($session->has('ticketsFilterData')) {
            $ticketFilters = $session->get('ticketsFilterData');
        } else {
            $ticketFilters = new TicketFilters();
            $ticketFilters->setServiceType(array(0));
        }

        $form = $this->createForm(
            new TicketFiltersType($this->container->get('security.context')),
            $ticketFilters,
            array(
                'method'		=>	'GET',
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Data is valid so save it in session for another request
            $session->set('ticketsFilterData', $form->getData());

            if ($request->query->get('limit') != null)
                $session->set('limit', $request->query->get('limit', 10));
        }

        $session = $this->get('session');
        if ($request->query->get('limit') != null)
            $session->set('limit', $request->query->get('limit', 10));

        $subQueryDQL = $this->getDoctrine()->getManager()->createQueryBuilder();
        $subQueryDQL = $subQueryDQL->select($subQueryDQL->expr()->max('ta2.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta2')
            ->where($subQueryDQL->expr()->eq('ta2.ticket', 't.id'))
            ->getDQL();

        $allowedDepartments = $this->getUser()->getDepartmentsIds();
        $allowedDepartments[] = null;

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb = $qb->select('t.id', 't.announcedBy', 't.announcedAt', 'IDENTITY(sv.department) as department', 't.fixedAt', 't.description', 't.isReal', 't.isClosed')
            ->from('TltTicketBundle:Ticket', 't')
            ->innerJoin('t.ticketAllocations', 'ta', 'WITH', $qb->expr()->eq('ta.insertedAt', '(' . $subQueryDQL . ')') )
            ->leftJoin('t.equipment', 'e')
            ->leftJoin('e.service', 'sv');
        $qb->andWhere('ta.branch IN (:userBranches)');
        $qb->setParameter('userBranches', $this->getUser()->getBranchesIds());


        if ($ticketFilters->getServiceType() !== null && count($ticketFilters->getServiceType()) > 0)
        {
            if (in_array('0', $ticketFilters->getServiceType())) {
                $qb->andWhere('sv.department IN (:userDepartments) OR sv.department IS NULL');
                $qb->setParameter('userDepartments', $ticketFilters->getServiceType());
            } else {
                $qb->andWhere('sv.department IN (:userDepartments)');
                $qb->setParameter('userDepartments', $ticketFilters->getServiceType());
            }
        } else {
            $qb->andWhere('sv.department=99999');
        }


        if ($ticketFilters->getSearch() !== null)
        {
            $qb->andWhere(
                $qb->expr()->like('t.id', ':id') . ' OR ' . $qb->expr()->like('t.announcedBy', ':announcedBy')
            );
            $qb->setParameter('id', $ticketFilters->getSearch());
            $qb->setParameter('announcedBy', '%' . $ticketFilters->getSearch() . '%' );
        }

        $qb->orderBy('t.id', 'DESC');

        $paginator  = $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(array('limit' => $session->get('limit', $request->query->get('limit', 10))));
        $pagination = $paginator->paginate(
            $qb,
            $request->query->get('page', 1)/*page number*/,
            $session->get('limit', $request->query->get('limit', 10)) /*limit per page*/
        );

		return $this->render(
			'TltTicketBundle:Default:index.html.twig',
			array(
                'form' => $form->createView(),
				'pagination' => $pagination,
			));
	}

    /**
     * @Route("/tickets/add", name="add_ticket")
     * @Template()
     */
    public function addAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_TICKET_INSERT')) {
            throw new AccessDeniedException();
        }

        $ticket = new Ticket();
        $ticket->setAnnouncedAt(new \DateTime());
        $ticket->setTransmissionType('telefon');
        $ticket->setTakenBy($this->getUser()->getLastname() . ' ' . $this->getUser()->getFirstname());

        $form = $this->createForm(
            new TicketType($this->container->get('security.context')),
            $ticket,
            array(
                'em' => $this->getDoctrine()->getManager(),
                'validation_groups' => array('insert'),
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user	=	$this->getUser();

            $ticket->setInsertedBy($user->getUsername());
            $ticket->setModifiedBy($user->getUsername());
            $ticket->setFromHost($this->container->get('request')->getClientIp());

            $ticket->updateTicketAllocation();

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();


            $mailer = $this->get('mailer');
            $message = $mailer->createMessage()
                ->setSubject('Tichet nou')
                ->setFrom('no-reply@teletrans.ro', 'Aplicatie SLA')
//                ->setTo($ticket->getTicketAllocations()->last()->getBranch()->getEmails())
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/ticket_new.html.twig
                        'Emails/ticket_new.html.twig',
                        array('ticket' => $ticket)
                    ),
                    'text/html'
                );

            $emails = explode(";", $ticket->getTicketAllocations()->last()->getBranch()->getEmails());
            foreach ($emails as $index => $email)
            {
                if ($index==0)
                    $message->setTo(trim($email));
                else {
//                    $message->addCc(trim($email));
                    $message->addTo(trim($email));
                }
            }

            $mailer->send($message);

            return $this->redirect(
                $this->generateUrl(
                    'success_ticket',
                    array(
                        'action'	=>	'add',
                        'id'		=>	$ticket->getId()
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
        $ticket = $this->getDoctrine()
            ->getRepository('TltTicketBundle:Ticket')
            ->findOneById($id);

        if ($ticket->getIsReal() != null)
        {
            throw new AccessDeniedException();
        }

        $ticketAllocation = new TicketAllocation();
		
		$form = $this->createForm( new TicketReallocateType($this->getUser()), $ticketAllocation );
		
		$form->handleRequest($request);
		
		if ($form->isValid()) {
            $user	=	$this->getUser();

            $ticketAllocation->setInsertedBy($user->getUsername());
            $ticketAllocation->setModifiedBy($user->getUsername());
            $ticketAllocation->setFromHost($this->container->get('request')->getClientIp());

//            $ticketAllocation->seTicketCreate( $ticketCreate );
            $ticketAllocation->seTicket( $ticket );

			// perform some action, such as saving the task to the database
			$em = $this->getDoctrine()->getManager();
			$em->persist($ticketAllocation);
			$em->flush();

            // TODO: de implementat trimiterea unui email.
			
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
//				'ticket' => $ticketCreate,
				'ticket' => $ticket,
				'form' => $form->createView()
			);	
	}

    /**
     * @Route("/tickets/details/{id}", name="ticket_details")
     * @Template("TltTicketBundle:Default:details.html.twig")
     */
    public function detailsAction(Request $request, $id)
    {
		$ticket = $this->getDoctrine()
			->getRepository('TltTicketBundle:Ticket')
			->findOneById($id);

        $defaultBackupSolution = $this->getDoctrine()
            ->getRepository('TltTicketBundle:BackupSolution')
            ->findOneById(1);

        $defaultOldness = $this->getDoctrine()
            ->getRepository('TltTicketBundle:Oldness')
            ->findOneById(2);

        $defaultEmergency = $this->getDoctrine()
            ->getRepository('TltTicketBundle:Emergency')
            ->findOneById(1);


        $transmissionTypes = $this->getDoctrine()
            ->getRepository('TltTicketBundle:TransmissionType')
            ->findAll();


        if ($ticket->getFixedBy() == null)
            $ticket->setFixedBy($this->getUser()->getFirstname() . ' ' . $this->getUser()->getLastname());

        if ($ticket->getCompartment() == null)
            $ticket->setCompartment($this->getUser()->getCompartment());

        if($ticket->getBackupSolution() == null)
            $ticket->setBackupSolution($defaultBackupSolution);

        if($ticket->getOldness() == null)
            $ticket->setOldness($defaultOldness);

        if($ticket->getEmergency() == null)
            $ticket->setEmergency($defaultEmergency);

        if ($ticket->getFixedAt() == null)
            $ticket->setFixedAt( new \DateTime());


        $templateOptions = array(
            'ticket' => $ticket,
            'transmissionTypes' => $transmissionTypes
        );


        $form = $this->createForm(
            new TicketType($this->container->get('security.context')),
            $ticket,
            array(
                'em' => $this->getDoctrine()->getManager(),
                'validation_groups' => array('solve'),
//                'method' => 'PATCH'
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($ticket->getIsReal()==1 && $ticket->getNotRealReason() != null) {
                $form->get('notRealReason')->addError(new FormError('Un tichet real nu poate avea motivatie ca nu este real'));
                $templateOptions['form'] = $form->createView();
                return $templateOptions;
            }

            if ($ticket->getFixedAt() < $ticket->getAnnouncedAt()) {
                    $form->get('fixedAt')->addError(new FormError('Data/Ora rezolvarii nu poate fi mai mica decat data/ora sesizarii'));
                    $templateOptions['form'] = $form->createView();
                    return $templateOptions;
            }

            if (count($ticket->getTicketMapping())==0) {
                $mappings = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:Mapping')
                    ->findOneByEquipment($ticket->getEquipment());

                if (count($mappings)==0)
                    $form->get('ticketMapping')->addError(new FormError('Nu a-ti facut nici o mapare pentru acest echipament. Mergeti la <a href="' . $this->generateUrl('admin_mappings_index', array('equipment_id' => $ticket->getEquipment()->getId())) . '">[ mapari ]</a> pentru a adauga cel putin o mapare!'));
                $form->get('ticketMapping')->addError(new FormError('Trebuie sa selectati cel putin un sistem.'));

                $templateOptions['form'] = $form->createView();
                return $templateOptions;
            }

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();

            foreach ($ticket->getTicketMapping() as $ticketMapping)
            {
                $ticketMapping->setResolvedIn(
                    $ticket->getWorkingTime(
                        $ticket->getAnnouncedAt(),
                        $ticket->getFixedAt(),
                        $ticketMapping->getMapping()->getSystem()->getGuaranteedValues()->first()
                    )
                );
            }

            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'success_ticket',
                    array(
                        'action' => 'fix',
                        'id' => $ticket->getId()
                    )
                )
            );
        }

        $templateOptions['form'] = $form->createView();

		return $templateOptions;
    }

	/**
     * @Route("/success_ticket/{action}/{id}", requirements={"id" = "\d+"}, name="success_ticket")
     * @Template()
     */
	public function successAction($action, $id = null)
	{
		$object = $objectParent = null;

		switch ($action)
		{
			case 'add':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:Ticket')
					->findOneById($id);
				break;
			case 'reallocate':
				$object = $this->getDoctrine()
					->getRepository('TltTicketBundle:TicketAllocation')
					->findOneById($id);
				break;
            case 'fix':
                $object = $this->getDoctrine()
                    ->getRepository('TltTicketBundle:Ticket')
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

    /**
     * @Route("/tickets/add_equipment/{id}", name="tickets_add_equipment")
     * @Template("TltTicketBundle:Default:add_equipment.html.twig")
     */
    public function addEquipmentAction(Request $request, $id)
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


        $ticketEquipment =  new TicketEquipment();
        $ticketEquipment->setDepartment($currentDepartment);
        $ticketEquipment->setService($currentService);
        $ticketEquipment->setBranch($currentZone);
        $ticketEquipment->setZoneLocation($currentZoneLocation);
        $ticketEquipment->setOwner($currentOwner);

        $form = $this->createForm(
            new TicketEquipmentType($this->getDoctrine()->getManager(), $this->getUser()),
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

        return
            array(
                'form' => $form->createView()
            );
    }

    /**
     * @Route("/tickets/blank-icr", name="tickets_blank_icr")
     * @Template("TltTicketBundle:Default:blank_icr.html.twig")
     */
    public function blankIcrAction()
    {
        $transmissionTypes = $this->getDoctrine()
            ->getRepository('TltTicketBundle:TransmissionType')
            ->findAll();

        return array(
            'ticket' => null,
            'transmissionTypes' => $transmissionTypes
        );
    }
}