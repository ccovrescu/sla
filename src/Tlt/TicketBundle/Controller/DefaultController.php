<?php
namespace Tlt\TicketBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Tlt\TicketBundle\Entity\Ticket;
use Tlt\TicketBundle\Entity\TicketAllocation;
use Tlt\TicketBundle\Entity\TicketEquipment;
use Tlt\TicketBundle\Entity\TicketMapping;
use Tlt\TicketBundle\Form\Type\Model\TicketFilters;
use Tlt\TicketBundle\Form\Type\TicketEquipmentType;
use Tlt\TicketBundle\Form\Type\TicketFiltersType;
use Tlt\TicketBundle\Form\Type\TicketReallocateType;
use Tlt\TicketBundle\Form\Type\TicketType;
use Tlt\TicketBundle\Model\ICRPDF;

use Twig_Template;

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
            TicketFiltersType::class,
            $ticketFilters,
            array(
                'method' => 'GET',
                'securityContext'=>$this->container->get('security.token_storage'),
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Data is valid so save it in session for another request
            $session->set('ticketsFilterData', $form->getData());

            if ($request->query->get('limit') != null) {
                $session->set('limit', $request->query->get('limit', 10));
            }
        }

        $session = $this->get('session');
        if ($request->query->get('limit') != null) {
            $session->set('limit', $request->query->get('limit', 10));
        }

        $subQueryDQL = $this->getDoctrine()->getManager()->createQueryBuilder();
        $subQueryDQL = $subQueryDQL->select($subQueryDQL->expr()->max('ta2.insertedAt'))
            ->from('TltTicketBundle:TicketAllocation', 'ta2')
            ->where($subQueryDQL->expr()->eq('ta2.ticket', 't.id'))
            ->getDQL();

        $allowedDepartments = $this->getUser()->getDepartmentsIds();
        $allowedDepartments[] = null;

        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb = $qb->select(
            't.id',
            'TRIM(CONCAT(an.lastname, \' \', an.firstname)) as announcedBy',
            't.announcedAt',
            'IDENTITY(sv.department) as department',
            't.fixedAt',
            't.description',
            't.isReal',
            't.isClosed'
        )
            ->from('TltTicketBundle:Ticket', 't')
            ->leftJoin('t.announcedBy', 'an')
            ->innerJoin('t.ticketAllocations', 'ta', 'WITH', $qb->expr()->eq('ta.insertedAt', '(' . $subQueryDQL . ')'))
            ->leftJoin('t.equipment', 'e')
            ->leftJoin('e.service', 'sv');
        $qb->andWhere('ta.branch IN (:userBranches)');
        $qb->setParameter('userBranches', $this->getUser()->getBranchesIds());


        if ($ticketFilters->getServiceType() !== null && count($ticketFilters->getServiceType()) > 0) {
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


        /**
         * La solicitarea Timisoarei (D.Mihalescu) s-a renuntat la afisarea tichetelor care nu sunt reale.
         */
        if ($ticketFilters->getIsReal() == null ||(count($ticketFilters->getServiceType()) > 0 && !in_array('1', $ticketFilters->getIsReal()))) {
                $qb->andWhere('t.isReal IS NULL OR t.isReal=1');
        }

        if ($ticketFilters->getSearch() !== null) {
            $qb->andWhere(
                $qb->expr()->like('t.id', ':id') . ' OR ' . $qb->expr()->like(
                    'CONCAT(an.firstname, \' \', an.lastname)',
                    ':announcedBy'
                )
            );
            $qb->setParameter('id', $ticketFilters->getSearch());
            $qb->setParameter('announcedBy', '%' . $ticketFilters->getSearch() . '%');
        }

        $qb->orderBy('t.id', 'DESC');

        $paginator = $this->get('knp_paginator');
        $paginator->setDefaultPaginatorOptions(
            array('limit' => $session->get('limit', $request->query->get('limit', 10)))
        );
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
            )
        );
    }

    /**
     * @Route("/tickets/add", name="add_ticket")
     * @Template()
     */
    public function addAction(Request $request)
    {
         if (!$this->get('security.authorization_checker')->isGranted('ROLE_TICKET_INSERT')) {
            throw new AccessDeniedException();
        }

        $ticket = new Ticket();
        $ticket->setAnnouncedAt(new \DateTime());
        $ticket->setTransmissionType('telefon');
        $ticket->setTakenBy($this->getUser()->getLastname() . ' ' . $this->getUser()->getFirstname());

        $form = $this->createForm(
            TicketType::class,
            $ticket,
            array(
                'em' => $this->getDoctrine()->getManager(),
                'validation_groups' => array('insert'),
                'securityContext'=>$this->container->get('security.token_storage'),
                'authorizationChecker'=>$this->get('security.authorization_checker')
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();

            $ticket->setInsertedBy($user->getUsername());
            $ticket->setModifiedBy($user->getUsername());
            $ticket->setFromHost($request->getClientIp());

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
                        array('ticket' => $ticket, 'agentia_veche'=>null)
                    ),
                    'text/html'
                );

            $emails = explode(";", $ticket->getTicketAllocations()->last()->getBranch()->getEmails());
            foreach ($emails as $index => $email) {
                if ($index == 0) {
                    $message->setTo(trim($email));
                } else {
//                    $message->addCc(trim($email));
                    $message->addTo(trim($email));
                }
            }

            $mailer->send($message);

            return $this->redirect(
                $this->generateUrl(
                    'success_ticket',
                    array(
                        'action' => 'add',
                        'id' => $ticket->getId()
                    )
                )
            );
        }

        return
            $this->render(
                'TltTicketBundle:Default:add.html.twig',
                array(
                    'ticket' => $ticket,
                    'form' => $form->createView(),
                )
            );
    }

    /**
     * @Route("/reallocate_ticket/{id}", name="reallocate_ticket")
     * @Template("TltTicketBundle:Default:reallocate.html.twig")
     */
    public function reallocateTicket(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_TICKET_INSERT')) {
            throw new AccessDeniedException();
        }

        $ticket = $this->getDoctrine()
            ->getRepository('TltTicketBundle:Ticket')
            ->findOneById($id);

        // introdus azi 07.05.2018 - trimitere de mail la realocare tichet
        $ticket_allocation_vechi = $this->getDoctrine()
            ->getRepository('TltTicketBundle:TicketAllocation')
            ->findOneBy(array('ticket'=>$id), array('id'=> 'DESC'));
        // in $branch_vechi stochez Agentia DIN CARE se realoca tichetul, pentru a o scrie in mail
        // pentru asta extrag din TichetAllocations ULTIMA inregistare avand ticket = $id (id-ul tichetului)
        $branch_vechi = $ticket_allocation_vechi->getBranch()->getName();
            // SFARSIT introdus azi 07.05.2018 - trimitere de mail la realocare tichet
        if ($ticket->getIsReal() != null) {
            throw new AccessDeniedException();
        }

        $ticketAllocation = new TicketAllocation();

        $form = $this->createForm(TicketReallocateType::class, $ticketAllocation,
            array('user'=>$this->getUser()));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();

            $ticketAllocation->setInsertedBy($user->getUsername());
            $ticketAllocation->setModifiedBy($user->getUsername());
            $ticketAllocation->setFromHost($request->getClientIp());

//            $ticketAllocation->seTicketCreate( $ticketCreate );
            $ticketAllocation->seTicket($ticket);

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticketAllocation);
            $em->flush();

            // introdus azi 07.05.2018 - trimitere de mail la realocare tichet

           // var_dump($branch_vechi);
            $mailer = $this->get('mailer');
            $message = $mailer->createMessage()
                ->setSubject('Tichet nou - realocat din '.$branch_vechi)
                ->setFrom('no-reply@teletrans.ro', 'Aplicatie SLA')
//                ->setTo($ticket->getTicketAllocations()->last()->getBranch()->getEmails())
                ->setBody(
                     $this->renderView(
                    // app/Resources/views/Emails/ticket_new.html.twig
                        'Emails/ticket_new.html.twig',
                        array('ticket' => $ticket, 'agentia_veche'=>$branch_vechi)
                    ),
                    'text/html'
                );

            $emails = explode(";", $ticket->getTicketAllocations()->last()->getBranch()->getEmails());
            foreach ($emails as $index => $email) {
                if ($index == 0) {
                    $message->setTo(trim($email));
                } else {
//                    $message->addCc(trim($email));
                    $message->addTo(trim($email));
                }
            }

             //$mailer->send($message);

            if ($mailer->send($message))
            {
                echo "Sent\n";
            }
            else
            {
                echo "Failed\n";
            }


            return $this->redirect(
                $this->generateUrl(
                    'success_ticket',
                    array(
                        'action' => 'add',
                        'id' => $ticket->getId()
                    )
                )
            );


            // pana aici introdus azi 07.05.2018
            // TODO: de implementat trimiterea unui email.
            // implementat de Claudiu la 07.05.2018
            return $this->redirect(
                $this->generateUrl(
                    'success_ticket',
                    array(
                        'action' => 'reallocate',
                        'id' => $ticketAllocation->getId()
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


        if ($ticket->getFixedBy() == null) {
            $ticket->setFixedBy($this->getUser()->getFirstname() . ' ' . $this->getUser()->getLastname());
        }

        if ($ticket->getCompartment() == null) {
            $ticket->setCompartment($this->getUser()->getCompartment());
        }

        if ($ticket->getBackupSolution() == null) {
            $ticket->setBackupSolution($defaultBackupSolution);
        }

        if ($ticket->getOldness() == null) {
            $ticket->setOldness($defaultOldness);
        }

        if ($ticket->getEmergency() == null) {
            $ticket->setEmergency($defaultEmergency);
        }

        if ($ticket->getFixedAt() == null) {
            $ticket->setFixedAt(new \DateTime());
        }


        $templateOptions = array(
            'ticket' => $ticket,
            'transmissionTypes' => $transmissionTypes
        );

        $form = $this->createForm(
            TicketType::class,
            $ticket,
            array(
                'em' => $this->getDoctrine()->getManager(),
                'validation_groups' => array('solve'),
                'securityContext'=>$this->container->get('security.token_storage'),
                'authorizationChecker'=>$this->get('security.authorization_checker')
//                'method' => 'PATCH'
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
//            echo "<script>alert('Am trecut pe aici formular valid');</script>";
            if ($ticket->getIsReal() == 1 && $ticket->getNotRealReason() != null) {
                $form->get('notRealReason')->addError(
                    new FormError('Un tichet real nu poate avea motivatie ca nu este real')
                );
                $templateOptions['form'] = $form->createView();

                return $templateOptions;
            }

            if ($ticket->getFixedAt() < $ticket->getAnnouncedAt()) {
                $form->get('fixedAt')->addError(
                    new FormError('Data/Ora rezolvarii nu poate fi mai mica decat data/ora sesizarii')
                );
                $templateOptions['form'] = $form->createView();

                return $templateOptions;
            }
            $today = date("Y-m-d H:i:s");
//            var_dump($today);

            if ($ticket->getFixedAt()->format("Y-m-d H:i:s") > $today) {
                $form->get('fixedAt')->addError(
                    new FormError('Data/Ora rezolvarii nu poate fi in viitor!')
                );
                $templateOptions['form'] = $form->createView();

                return $templateOptions;
            }


            if ($ticket->getIsReal() == 1) {
                if ($ticket->getEquipment() === null) {
                    $form->get('equipment')->addError(new FormError('Trebuie sa selectati cel putin un echipament.'));

                    $templateOptions['form'] = $form->createView();

                    return $templateOptions;
                }

                if (count($ticket->getTicketMapping()) == 0) {
                    $mappings = $this->getDoctrine()
                        ->getRepository('TltAdmnBundle:Mapping')
                        ->findOneByEquipment($ticket->getEquipment());

                    if (count($mappings) == 0) {
                        $form->get('ticketMapping')->addError(
                            new FormError(
                                'Nu ati facut nici o mapare pentru acest echipament. Mergeti la <a href="' . $this->generateUrl(
                                    'admin_mappings_index',
                                    array('equipment_id' => $ticket->getEquipment()->getId())
                                ) . '">[ mapari ]</a> pentru a adauga cel putin o mapare!'
                            )
                        );
                    }
                    $form->get('ticketMapping')->addError(new FormError('Trebuie sa selectati cel putin un sistem.'));

                    $templateOptions['form'] = $form->createView();

                    return $templateOptions;
                }
            }

// introdus 12.09.2018

            $campuri[] = $request->request->all();

/*            print_r($campuri);
            echo "<br>";

            $keys = array_keys($campuri);
            print_r($keys);
            echo "<br>";
            print_r(array_values($campuri));
            echo "<br>";
*/

              if (array_key_exists("ticketMapping",$campuri[0]["ticket"]))
              {
                  $sisteme_afectate = $campuri[0]["ticket"]["ticketMapping"] ;
                  echo "<script>alert('exista sisteme afectate');</script>";
                  echo "<script>alert($sisteme_afectate[0]);</script>";
              }
              else {
                  echo "<script>alert('NU exista sisteme afectate');</script>";
              }

            if (array_key_exists("total_afectate",$campuri[0]["ticket"]))
            {
                echo "<script>alert('exista sisteme TOTAL afectate');</script>";
                // matricea sistemelor (maparilor) TOTAL afectate
                $totalafectate = $campuri[0]["ticket"]["total_afectate"] ;
                $lungimea = count($totalafectate);
/*
                 for ($i=0; $i<$lungimea; $i++) {
                    echo ($totalafectate[$i]);
                    echo "<br>";
                }
                foreach ( ($totalafectate) as $total_afectate )
                {
                    print_r ($total_afectate);
                    echo "<br>";
                }

                $arrlength = count($totalafectate);

                for($x = 0; $x < $arrlength; $x++) {
                    $afectata = $totalafectate[$x];
                    echo ($afectata);
                    echo "<br>";
                }
                foreach($totalafectate as $x => $x_value) {
                    echo "Key=" . $x . ", Value=" . $x_value;
                    echo "<br>";
                }
                echo "<script>alert('Aici ');</script>";
*/
            foreach ($ticket->getTicketMapping() as $ticketMapping) {
                        $ticketMappingId=$ticketMapping->getMapping()->getId();
//                        echo "ticketMappingId = ".$ticketMappingId;
                        if (in_array($ticketMappingId, $totalafectate) )
                        {
                            //and in_array($ticketMappingId, $sisteme_afectate)
//                            echo "ID ul se gaseste in totalafectate !!"."<br>";
                            $ticketMapping->setTotalaffected(true);
                        }
                        else
                        {
                            $ticketMapping->setTotalaffected(false);
                        }
                    }
            }
            else {
                echo "Nu exista cheia"."<br>";
                 echo"<script>alert('Nu exista cheia');</script>";
                foreach ($ticket->getTicketMapping() as $ticketMapping)
                {
                   $ticketMapping->setTotalaffected(false);
                }
            }


//            die();
// sfarsit introdus 12.09.2018

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();

            foreach ($ticket->getTicketMapping() as $ticketMapping) {
                $ticketMapping->setResolvedIn(
                    $ticket->getWorkingTime(
                        $ticket->getAnnouncedAt(),
                        $ticket->getFixedAt(),
                        $ticketMapping->getMapping()->getSystem()->getGuaranteedValues()->first()
                    )
                );
                // introdus 13.09.2018
                // sf introdus 13.09.2018
            }

/*
// introdus 16.09.2018

            $data = $request->request->all();

            print("REQUEST DATA<br/>");
            foreach ($data as $k => $d) {
                print("$k: <pre>"); print_r($d); print("</pre>");
            }

            $children = $form->all();

            print("<br/>FORM CHILDREN<br/>");
            foreach ($children as $ch) {
                print($ch->getName() . "<br/>");
            }

            $data = array_diff_key($data, $children);
//$data contains now extra fields

            print("<br/>DIFF DATA<br/>");
            foreach ($data as $k => $d) {
                print("$k: <pre>"); print_r($d); print("</pre>");
            }
//die();
// sf introdus 16.09.2018
*/
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
        else
        {  // $string = (string) $form->getErrors(true, false);
           // var_dump($string);
//            echo "<script>alert('Am trecut pe aici formular INvalid');</script>";
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

        switch ($action) {
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
                'action' => $action,
                'ticket' => $object,
                'parent' => $objectParent
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

        $response = $this->forward(
            'TltAdmnBundle:Ajax:filterServices',
            array(
                'request' => $req
            )
        );

        $currentService = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Service')
            ->findOneById(json_decode($response->getContent())[0]->id);


        /* Finding first zone of current user. */
        $req = new Request();
        $req->request->set('department_id', $currentDepartment->getId());
        $req->request->set('service_id', $currentService->getId());

        $response = $this->forward(
            'TltAdmnBundle:Ajax:filterBranches',
            array(
                'request' => $req
            )
        );

        $currentZone = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Branch')
            ->findOneById(json_decode($response->getContent())[0]->id);


        /* Finding first zoneLocation of current user. */
        $req = new Request();
        $req->request->set('branch_id', $currentZone->getId());
        $req->request->set('service_id', $currentService->getId());

        $response = $this->forward(
            'TltAdmnBundle:Ajax:filterLocations',
            array(
                'request' => $req
            )
        );

        $currentZoneLocation = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:ZoneLocation')
            ->findOneById(json_decode($response->getContent())[0]->id);


        /* Finding first owner of current user. */
        $req = new Request();
        $req->request->set('department_id', $currentDepartment->getId());
        $req->request->set('service_id', $currentService->getId());
        $req->request->set('branch_id', $currentZone->getId());
        $req->request->set('location_id', $currentZoneLocation->getId());

        $response = $this->forward(
            'TltAdmnBundle:Ajax:filterOwners',
            array(
                'request' => $req
            )
        );

        $currentOwner = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Owner')
            ->findOneById(json_decode($response->getContent())[0]->id);


        $ticketEquipment = new TicketEquipment();
        $ticketEquipment->setDepartment($currentDepartment);
        $ticketEquipment->setService($currentService);
        $ticketEquipment->setBranch($currentZone);
        $ticketEquipment->setZoneLocation($currentZoneLocation);
        $ticketEquipment->setOwner($currentOwner);

        $form = $this->createForm(
            new TicketEquipmentType($this->getDoctrine()->getManager(), $this->getUser()),
            $ticketEquipment,
            array(
                'department' => true,
                'service' => true,
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
//        $transmissionTypes = $this->getDoctrine()
//            ->getRepository('TltTicketBundle:TransmissionType')
//            ->findAll();
//
//        return array(
//            'ticket' => null,
//            'transmissionTypes' => $transmissionTypes
//        );

        $pdf = $this->icr();

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output('icr.pdf', 'I');
    }

    /**
     * @Route("/tickets/print/{id}", name="tickets_print", requirements={"id"="\d+"})
     * @Template()
     *
     * @param integer $id
     */
    public function printAction($id)
    {
        $pdf = $this->icr($id);

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output('icr.pdf', 'I');
    }


    public function icrBradescu($id = null)
    {
        $ticket = null;
        if ($id != null) {
            $ticket = $this->getDoctrine()
                ->getRepository('TltTicketBundle:Ticket')
                ->findOneBy(
                    ['id' => $id]
                );
        }

        define('K_PATH_IMAGES', $this->get('kernel')->getRootDir());

        // create new PDF document
        $pdf = new ICRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TELETRANS SA');
        $pdf->SetTitle('Fisa ' . ($ticket ? $ticket->getId() : ''));
        $pdf->SetSubject('Fisa de interventie');
        $pdf->SetKeywords('Fisa, interventie, ' . ($ticket ? $ticket->getId() : ''));

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // ------------

        $pdf->SetFont('times', 'B', 12, '', true);
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), 'FISA DE INTERVENTIE<sup><em>1</em></sup>', 0, 1, false, true, 'C');

        $startY = $pdf->GetY();

        // row 1
        $pdf->SetFont('times', 'I', 10, '', true);
        $pdf->setCellMargins(0.5, 0.5, 0.25, 0.25);
        $pdf->MultiCell(65, 16, "Departamentul TELETRANS\n(DTI/DTC/DIP/SCL)\n..................", 1, 'C',false,0,'','',true,0,false,true,16,'M');

        $pdf->setCellMargins(0.25, 0.5, 0.25, 0.25);
        $pdf->MultiCell(68.5, 16, "Entitate TELETRANS (Executiv/Agentie/Centru)\n.................................", 1, 'C',false,0,'','',true,0,false,true,16,'M');

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getEquipment()!= null)
            if ($ticket and $ticket->getEmergency()->getId() == 1) {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 15,
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
            } else {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 15,
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
            }
        else {
            $pdf->writeHTMLCell(14, 6, $x+15, $y+6,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
            $pdf->writeHTMLCell(14, 6, $pdf->GetX(), $y+6,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
        }

        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.25, 0.5, 0.5, 0.25);
        $pdf->MultiCell(44.5, 16, "Urgent:          DA            NU", 1, 'L',false,1,'','',true,0,false,true,16,'M');

        // row 2
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(35, 11, "Nr. SLA: ..............", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.25, 0.25);
        $pdf->MultiCell(55, 11, "Data/Ora sesizarii:\n....................................", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(88, 11, "Persoana din tura care preia sesizarea:\n.....................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 3
        $transmissionTypes = $this->getDoctrine()->getRepository('TltTicketBundle:TransmissionType')->findAll();

        $transmissions  = '(';
        foreach ($transmissionTypes as $transmission)
        {
            if ($transmissions[strlen($transmissions)-1] != '(')
                $transmissions .= '/';
            $transmissions .= (($ticket == null or $transmission==$ticket->getTransmissionType()) ? ucfirst($transmission->getName()) : '<strike>' . ucfirst($transmission->getName()) . '</strike>');
        }
        $transmissions .= ')';
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
//        $pdf->MultiCell(90.5, 11, "Mod de transmitere sesizare:\n" . $transmissions, 1, 'C',false,0,'','',true,0,false,true,11,'T');
        $pdf->writeHTMLCell(90.5, 11, $pdf->GetX(), $pdf->GetY(), "Mod de transmitere sesizare:<br/>" . $transmissions, 1, 0, false, true, 'C');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(88, 11, "Persoana anuntata:\n.....................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 4
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(90.5, 11, "Sesizare lansata de: (Ex/DEN/DET/ST/OMEPA)\n.................................................................", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(88, 11, "Persoana care face sesizarea:\n.....................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 5
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 24, "Descriere pe scurt deranjament:", 1, 'L',false,1,'','',true,0,false,true,24,'T');

        // row 6
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getIsReal()!= null)
            if ($ticket->getIsReal() == true ) {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 32,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
            } else {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 32,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
            }
        else {
            $pdf->writeHTMLCell(
                14,
                6,
                $x + 32,
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
            $pdf->writeHTMLCell(
                14,
                6,
                $pdf->GetX(),
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
        }

        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(60.5, 11, "Sesizarea este reala:         DA            NU", 1, 'L',false,0,'','',true,0,false,true,11,'T');

        $notRealReasonX = $pdf->getX();
        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(118, 11, "De ce NU este reala:\n................................................................................................................", 1, 'L',false,1,'','',true,0,false,true,11,'T');

        // row 7
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(50.5, 11, "Tip interventie:\n...........................................", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(128, 11, "Compartimentul din cadrul TELETRANS implicat in rezolvarea sesizarii:\n............................................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 7
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getIsReal()!= null)
            if ($ticket->getOldness() and $ticket->getOldness()->getId() == 2 ) {
                $pdf->writeHTMLCell(
                    18,
                    6,
                    $x + 33,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    23,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
            } else {
                $pdf->writeHTMLCell(
                    18,
                    6,
                    $x + 33,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    23,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
            }
        else {
            $pdf->writeHTMLCell(
                18,
                6,
                $x + 33,
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
            $pdf->writeHTMLCell(
                23,
                6,
                $pdf->GetX(),
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
        }


        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(75.5, 6, "Vechime echipament:        1-3 ani          peste 3 ani", 1, 'L',false,0,'','',true,0,false,true,6,'T');

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getIsReal() != null) {
            switch ($ticket->getBackupSolution()->getId())
            {
                case 1:
                    $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    break;
                case 2:
                    $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    break;
                case 3:
                    $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',0,0);
                    break;
            }
        } else {
            $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
            $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
            $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
        }

        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(103, 6, "S-a asigurat solutie de rezerva:        Da         Nu          Nu este cazul", 1, 'L',false,1,'','',true,0,false,true,6,'T');

        // row 8
        $equipmentY = $pdf->GetY();
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 6, "Echipament: ......................................................................................................................................", 1, 'L',false,1,'','',true,0,false,true,6,'T');

        // row 9
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 24, "Sisteme afectate:", 1, 'L',false,1,'','',true,0,false,true,24,'T');

        // row 10
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(100.5, 6, "Persoana care rezolva: ..........................................", 1, 'L',false,0,'','',true,0,false,true,6,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(78, 6, "Data/Ora rezolvarii: ...............................", 1, 'L',false,1,'','',true,0,false,true,6,'T');

        // row 11
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 30, "Mod de rezolvare:", 1, 'L',false,1,'','',true,0,false,true,30,'T');

        // row 11
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 24, "Resurse utilizate:", 1, 'L',false,1,'','',true,0,false,true,24,'T');

        $pdf->SetLineStyle(array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
        $pdf->Rect(
            $pdf->getMargins()['left'],
            $startY,
            $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'],
            $pdf->GetY() - $startY + 0.25
        );

        $pdf->Cell(0, 0, 'Anexe: ............................................................................................................', 0, 1, 'L', 0, '', 0);

        $pdf->Ln();

        $pdf->SetX(66);
        $pdf->Cell(42,0,'Executant(i)',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'Avizat Manager',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'Avizat Beneficiar',0,1,'C',0,'',0);

        $pdf->Cell(50,0,'Nume:',0,0,'L',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,1,'C',0,'',0);

        $pdf->Cell(50,0,'Semnatura:',0,0,'L',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,1,'C',0,'',0);

        $pdf->Ln();

        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+70, $pdf->GetY(), array());
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), '<sup><em>1</em></sup>Se completeaza conform procedurilor specifice fiecarui departament', 0, 1, false, true, 'L');


        if ($ticket != null) {
            $pdf->SetY($startY+8.5);

            if ($ticket->getEquipment() and $ticket->getEquipment() != null) {
                $pdf->setCellMargins(0.5, 0.5, 0.25, 0.25);
                switch ($ticket->getEquipment()->getService()->getDepartment()->getId())
                {
                    case 1:
                        $pdf->MultiCell(65, 6, "DIP", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 2:
                        $pdf->MultiCell(65, 6, "DTC", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 3:
                        $pdf->MultiCell(65, 6, "DTI", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 4:
                        $pdf->MultiCell(65, 6, "SCL CONT", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 5:
                        $pdf->MultiCell(65, 6, "DTC", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 6:
                        $pdf->MultiCell(65, 6, "DTI", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 7:
                        $pdf->MultiCell(65, 6, "DTC", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 8:
                        $pdf->MultiCell(65, 6, "DTI", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;


                }
            }

            $pdf->setCellMargins(0.25, 0.5, 0.25, 0.25);
            $pdf->MultiCell(68.5, 6, $ticket->getTicketAllocations()->first()->getBranch()->getName(), 0, 'C',false,1,'','',true,0,false,true,6,'M');

            $pdf->SetX(33);
            $pdf->SetY($pdf->GetY()+1, false);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(13, 6, $ticket->getId(), 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $pdf->SetX($pdf->GetX()+4);
            $pdf->SetY($pdf->GetY()+4.5, false);
            $pdf->setCellMargins(0.25, 0.25, 0.25, 0.25);
            $pdf->MultiCell(55, 6, $ticket->getAnnouncedAt()->format('d.m.Y H:i'), 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $lastX = $pdf->GetX();
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(88, 6, $ticket->getTakenBy(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetX($lastX);
            $pdf->SetY($pdf->GetY()+4.5, false);
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(88, 6, $ticket->getAnnouncedTo(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetY($pdf->GetY()+5);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(90.5, 6, ($ticket->getEquipment() ? $ticket->getEquipment()->getOwner()->getName() : ''), 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(88, 6, $ticket->getAnnouncedBy(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetFont('times', 'BI', 10, '', true);
            $pdf->SetY($pdf->GetY()+5.55, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 20, $ticket->getDescription(), 0, 'L',false,1,'','',true,0,false,true,20,'T',true);

            $pdf->SetFont('times', 'I', 10, '', true);
            $pdf->SetX($notRealReasonX);
            $pdf->setY($pdf->GetY()+4,false);
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(118, 6, $ticket->getNotRealReason() ? : '', 0, 'L',false,1,'','',true,0,false,true,6,'T', true);

            $pdf->setY($pdf->GetY()+4.5);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(50.5, 6, $ticket->getTicketType() ? : '', 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(128, 6, $ticket->getCompartment(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetX(35);
            $pdf->SetY($equipmentY-0.5, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(159, 6, $ticket->getEquipment() ? : '', 0, 'L',false,1,'','',true,0,false,true,6,'T');

            $systems = '';
            $mappings = $ticket->getTicketMapping()->toArray();

            /** @var TicketMapping $mapping */
            foreach ($mappings as $mapping)
            {
                if (strlen($systems)>0)
                    $systems .= ', ';
                $systems .= $mapping->getMapping()->getSystem()->getName();
            }

            $pdf->SetY($pdf->GetY()+4.5);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 20, $systems, 0, 'L',false,1,'','',true,0,false,true,20,'T');

            $pdf->SetX(50);
            $pdf->SetY($pdf->GetY()-0.5,false);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(65.5, 6, $ticket->getFixedBy(), 0, 'L',false,0,'','',true,0,false,true,6,'T');

            $pdf->SetX($pdf->GetX()+30);
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(48, 6, $ticket->getFixedAt() ? $ticket->getFixedAt()->format('d.m.Y H:i') : '', 0, 'L',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetFont('times', 'BI', 10, '', true);
            $pdf->SetY($pdf->GetY()+5, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 25.5, $ticket->getFixedMode(), 0, 'L',false,1,'','',true,0,false,true,25.5,'T',true);

            $pdf->SetY($pdf->GetY()+5, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 19, $ticket->getResources(), 0, 'L',false,1,'','',true,0,false,true,19,'T',true);
        }

        return $pdf;
    }

    public function icr($id = null)
    {
        $ticket = null;
        if ($id != null) {
            $ticket = $this->getDoctrine()
                ->getRepository('TltTicketBundle:Ticket')
                ->findOneBy(
                    ['id' => $id]
                );
        }

        define('K_PATH_IMAGES', $this->get('kernel')->getRootDir());

        // create new PDF document
        $pdf = new ICRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TELETRANS SA');
        $pdf->SetTitle('Fisa ' . ($ticket ? $ticket->getId() : ''));
        $pdf->SetSubject('Fisa de interventie');
        $pdf->SetKeywords('Fisa, interventie, ' . ($ticket ? $ticket->getId() : ''));

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // ------------

        $pdf->SetFont('times', 'B', 12, '', true);
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), 'FISA DE INTERVENTIE<sup><em>1</em></sup>', 0, 1, false, true, 'C');

        $startY = $pdf->GetY();

        // row 1
        $pdf->SetFont('times', 'I', 10, '', true);
        $pdf->setCellMargins(0.5, 0.5, 0.25, 0.25);
        $pdf->MultiCell(65, 16, "Departamentul TELETRANS\n(DTI/DTC/DIP/SCL)\n..................", 1, 'C',false,0,'','',true,0,false,true,16,'M');

        $pdf->setCellMargins(0.25, 0.5, 0.25, 0.25);
        $pdf->MultiCell(68.5, 16, "Entitate TELETRANS (Executiv/Agentie/Centru)\n.................................", 1, 'C',false,0,'','',true,0,false,true,16,'M');

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getEquipment()!= null)
            if ($ticket and $ticket->getEmergency()->getId() == 1) {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 15,
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
            } else {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 15,
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y + 6,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
            }
        else {
            $pdf->writeHTMLCell(14, 6, $x+15, $y+6,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
            $pdf->writeHTMLCell(14, 6, $pdf->GetX(), $y+6,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
        }

        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.25, 0.5, 0.5, 0.25);
        $pdf->MultiCell(44.5, 16, "Urgent:          DA            NU", 1, 'L',false,1,'','',true,0,false,true,16,'M');

        // row 2
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(35, 11, "Nr. SLA: ..............", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.25, 0.25);
        $pdf->MultiCell(55, 11, "Data/Ora sesizarii:\n....................................", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(88, 11, "Persoana din tura care preia sesizarea:\n.....................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 3
        $transmissionTypes = $this->getDoctrine()->getRepository('TltTicketBundle:TransmissionType')->findAll();

        $transmissions  = '(';
        foreach ($transmissionTypes as $transmission)
        {
            if ($transmissions[strlen($transmissions)-1] != '(')
                $transmissions .= '/';
            $transmissions .= (($ticket == null or $transmission==$ticket->getTransmissionType()) ? ucfirst($transmission->getName()) : '<strike>' . ucfirst($transmission->getName()) . '</strike>');
        }
        $transmissions .= ')';
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
//        $pdf->MultiCell(90.5, 11, "Mod de transmitere sesizare:\n" . $transmissions, 1, 'C',false,0,'','',true,0,false,true,11,'T');
        $pdf->writeHTMLCell(90.5, 11, $pdf->GetX(), $pdf->GetY(), "Mod de transmitere sesizare:<br/>" . $transmissions, 1, 0, false, true, 'C');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(88, 11, "Persoana anuntata:\n.....................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 4
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(90.5, 11, "Sesizare lansata de: (Ex/DEN/DET/ST/OMEPA)\n.................................................................", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(88, 11, "Persoana care face sesizarea:\n.....................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 5
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 24, "Descriere pe scurt deranjament:", 1, 'L',false,1,'','',true,0,false,true,24,'T');

        // row 6
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getIsReal()!= null)
            if ($ticket->getIsReal() == true ) {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 32,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
            } else {
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $x + 32,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    14,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
            }
        else {
            $pdf->writeHTMLCell(
                14,
                6,
                $x + 32,
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
            $pdf->writeHTMLCell(
                14,
                6,
                $pdf->GetX(),
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
        }

        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(60.5, 11, "Sesizarea este reala:         DA            NU", 1, 'L',false,0,'','',true,0,false,true,11,'T');

        $notRealReasonX = $pdf->getX();
        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(118, 11, "De ce NU este reala:\n................................................................................................................", 1, 'L',false,1,'','',true,0,false,true,11,'T');

        // row 7
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(50.5, 11, "Tip interventie:\n...........................................", 1, 'C',false,0,'','',true,0,false,true,11,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(128, 11, "Compartimentul din cadrul TELETRANS implicat in rezolvarea sesizarii:\n............................................................................", 1, 'C',false,1,'','',true,0,false,true,11,'T');

        // row 7
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getIsReal()!= null)
            if ($ticket->getOldness() and $ticket->getOldness()->getId() == 2 ) {
                $pdf->writeHTMLCell(
                    18,
                    6,
                    $x + 33,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    23,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
            } else {
                $pdf->writeHTMLCell(
                    18,
                    6,
                    $x + 33,
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',
                    0,
                    0
                );
                $pdf->writeHTMLCell(
                    23,
                    6,
                    $pdf->GetX(),
                    $y+0.5,
                    '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                    0,
                    0
                );
            }
        else {
            $pdf->writeHTMLCell(
                18,
                6,
                $x + 33,
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
            $pdf->writeHTMLCell(
                23,
                6,
                $pdf->GetX(),
                $y+0.5,
                '<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',
                0,
                0
            );
        }


        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(75.5, 6, "Vechime echipament:        1-3 ani          peste 3 ani", 1, 'L',false,0,'','',true,0,false,true,6,'T');

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        if ($ticket and $ticket->getIsReal() != null) {
            switch ($ticket->getBackupSolution()->getId())
            {
                case 1:
                    $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    break;
                case 2:
                    $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    break;
                case 3:
                    $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
                    $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/checked.jpg">',0,0);
                    break;
            }
        } else {
            $pdf->writeHTMLCell(12, 6, $x+46, $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
            $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
            $pdf->writeHTMLCell(12, 6, $pdf->GetX(), $y+0.5,'<img src="' . K_PATH_IMAGES . '/../web/css/unchecked.jpg">',0,0);
        }

        $pdf->SetX($x);
        $pdf->SetY($y, false);

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(103, 6, "S-a asigurat solutie de rezerva:        Da         Nu          Nu este cazul", 1, 'L',false,1,'','',true,0,false,true,6,'T');

        // row 8
        $equipmentY = $pdf->GetY();
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 6, "Echipament: ......................................................................................................................................", 1, 'L',false,1,'','',true,0,false,true,6,'T');

        // row 9
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 24, "Sisteme afectate:", 1, 'L',false,1,'','',true,0,false,true,24,'T');
// introdus 18.09.2018

        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 10, "Sisteme TOTAL afectate:", 1, 'L',false,1,'','',true,0,false,true,10,'T');

// sf introdus 18.09.2018
        // row 10
        $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
        $pdf->MultiCell(100.5, 6, "Persoana care rezolva: ..........................................", 1, 'L',false,0,'','',true,0,false,true,6,'T');

        $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
        $pdf->MultiCell(78, 6, "Data/Ora rezolvarii: ...............................", 1, 'L',false,1,'','',true,0,false,true,6,'T');

        // row 11
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 30, "Mod de rezolvare:", 1, 'L',false,1,'','',true,0,false,true,30,'T');

        // row 11
        $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
        $pdf->MultiCell(179, 20, "Resurse utilizate:", 1, 'L',false,1,'','',true,0,false,true,20,'T');

        $pdf->SetLineStyle(array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
        $pdf->Rect(
            $pdf->getMargins()['left'],
            $startY,
            $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'],
            $pdf->GetY() - $startY + 0.25
        );

        $pdf->Cell(0, 0, 'Anexe: ............................................................................................................', 0, 1, 'L', 0, '', 0);

        $pdf->Ln();

        $pdf->SetX(66);
        $pdf->Cell(42,0,'Executant(i)',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'Avizat Manager',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'Avizat Beneficiar',0,1,'C',0,'',0);

        $pdf->Cell(50,0,'Nume:',0,0,'L',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,1,'C',0,'',0);

        $pdf->Cell(50,0,'Semnatura:',0,0,'L',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,0,'C',0,'',0);
        $pdf->Cell(42,0,'...................................',0,1,'C',0,'',0);

        $pdf->Ln();

        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+70, $pdf->GetY(), array());
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), '<sup><em>1</em></sup>Se completeaza conform procedurilor specifice fiecarui departament', 0, 1, false, true, 'L');


        if ($ticket != null) {
            $pdf->SetY($startY+8.5);

            if ($ticket->getEquipment() and $ticket->getEquipment() != null) {
                $pdf->setCellMargins(0.5, 0.5, 0.25, 0.25);
                switch ($ticket->getEquipment()->getService()->getDepartment()->getId())
                {
                    case 1:
                        $pdf->MultiCell(65, 6, "DIP", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 2:
                        $pdf->MultiCell(65, 6, "DTC", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 3:
                        $pdf->MultiCell(65, 6, "DTI", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 4:
                        $pdf->MultiCell(65, 6, "SCL CONT", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 5:
                        $pdf->MultiCell(65, 6, "DTC", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 6:
                        $pdf->MultiCell(65, 6, "DTI", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 7:
                        $pdf->MultiCell(65, 6, "DTC", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;
                    case 8:
                        $pdf->MultiCell(65, 6, "DTI", 0, 'C',false,0,'','',true,0,false,true,6,'M');
                        break;


                }
            }

            $pdf->setCellMargins(0.25, 0.5, 0.25, 0.25);
            $pdf->MultiCell(68.5, 6, $ticket->getTicketAllocations()->first()->getBranch()->getName(), 0, 'C',false,1,'','',true,0,false,true,6,'M');

            $pdf->SetX(33);
            $pdf->SetY($pdf->GetY()+1, false);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(13, 6, $ticket->getId(), 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $pdf->SetX($pdf->GetX()+4);
            $pdf->SetY($pdf->GetY()+4.5, false);
            $pdf->setCellMargins(0.25, 0.25, 0.25, 0.25);
            $pdf->MultiCell(55, 6, $ticket->getAnnouncedAt()->format('d.m.Y H:i'), 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $lastX = $pdf->GetX();
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(88, 6, $ticket->getTakenBy(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetX($lastX);
            $pdf->SetY($pdf->GetY()+4.5, false);
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(88, 6, $ticket->getAnnouncedTo(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetY($pdf->GetY()+5);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(90.5, 6, ($ticket->getEquipment() ? $ticket->getEquipment()->getOwner()->getName() : ''), 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(88, 6, $ticket->getAnnouncedBy(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetFont('times', 'BI', 10, '', true);
            $pdf->SetY($pdf->GetY()+5.55, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 20, $ticket->getDescription(), 0, 'L',false,1,'','',true,0,false,true,20,'T',true);

            $pdf->SetFont('times', 'I', 10, '', true);
            $pdf->SetX($notRealReasonX);
            $pdf->setY($pdf->GetY()+4,false);
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(118, 6, $ticket->getNotRealReason() ? : '', 0, 'L',false,1,'','',true,0,false,true,6,'T', true);

            $pdf->setY($pdf->GetY()+4.5);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(50.5, 6, $ticket->getTicketType() ? : '', 0, 'C',false,0,'','',true,0,false,true,6,'T');

            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(128, 6, $ticket->getCompartment(), 0, 'C',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetX(35);
            $pdf->SetY($equipmentY-0.5, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(159, 6, $ticket->getEquipment() ? : '', 0, 'L',false,1,'','',true,0,false,true,6,'T');

            $systems = '';
            $mappings = $ticket->getTicketMapping()->toArray();

            /** @var TicketMapping $mapping */
            foreach ($mappings as $mapping)
            {
                if (strlen($systems)>0)
                    $systems .= ', ';
                $systems .= $mapping->getMapping()->getSystem()->getName();
            }

            $pdf->SetY($pdf->GetY()+4.5);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 20, $systems, 0, 'L',false,1,'','',true,0,false,true,20,'T');

// introdus la 17.09.2018
            $systeme_total_afectate = '';
            $mappings_total_affected = $ticket->TotalAffectedSystems()->toArray();
//            var_dump($mappings_total_affected);
//            die();

            foreach ($mappings_total_affected as $mapping)
            {
                if (strlen($systeme_total_afectate)>0)
                    $systeme_total_afectate .= ', ';
                $systeme_total_afectate .= $mapping->getMapping()->getSystem()->getName();
            }
            $pdf->SetY($pdf->GetY()+4.5);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 10, $systeme_total_afectate, 0, 'L',false,1,'','',true,0,false,true,10,'T');

//        var_dump($systeme_total);
//            die();
// sf introdus la 17.09.2018

            $pdf->SetX(50);
            $pdf->SetY($pdf->GetY()-5.0,false);
            $pdf->setCellMargins(0.5, 0.25, 0.25, 0.25);
            $pdf->MultiCell(65.5, 6, $ticket->getFixedBy(), 0, 'L',false,0,'','',true,0,false,true,6,'T');

            $pdf->SetX($pdf->GetX()+30);
            $pdf->setCellMargins(0.25, 0.25, 0.5, 0.25);
            $pdf->MultiCell(48, 6, $ticket->getFixedAt() ? $ticket->getFixedAt()->format('d.m.Y H:i') : '', 0, 'L',false,1,'','',true,0,false,true,6,'T');

            $pdf->SetFont('times', 'BI', 10, '', true);
            $pdf->SetY($pdf->GetY()+5, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 25.5, $ticket->getFixedMode(), 0, 'L',false,1,'','',true,0,false,true,25.5,'T',true);

            $pdf->SetY($pdf->GetY()+5, false);
            $pdf->setCellMargins(0.5, 0.25, 0.5, 0.25);
            $pdf->MultiCell(179, 19, $ticket->getResources(), 0, 'L',false,1,'','',true,0,false,true,19,'T',true);
        }

        return $pdf;
    }


}



