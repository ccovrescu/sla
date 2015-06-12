<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Announcer;
use Tlt\AdmnBundle\Form\Type\AnnouncerType;

use Symfony\Component\HttpFoundation\JsonResponse;
use Tlt\AdmnBundle\Entity\AnnouncerFilter;
use Tlt\AdmnBundle\Form\Type\AnnouncerFilterType;
use Tlt\AdmnBundle\Entity\Owner;


class AnnouncerController extends Controller
{
    /**
     * @Route("/announcers/index", name="tlt_admin_announcers_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $filter = new AnnouncerFilter();
        $form = $this->createForm(
            new AnnouncerFilterType($this->getDoctrine()->getManager(), $this->get('security.context')),
            $filter
        );

        $form->handleRequest($request);

        $announcers = null;

        if ($form->isValid())
        {
            if ($filter->getBranch() != null)
            {
                $announcers = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:Announcer')
                    ->findBy(
                        array('branch' => $filter->getBranch()),
                        array(
                            'firstname' => 'ASC',
                            'lastname' => 'ASC',
                            'compartment' => 'ASC'
                        )
                    );
            } else {
                $announcers = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:Announcer')
                    ->findBy(
                        array('branch' => $this->getUser()->getBranchesIds()),
                        array(
                            'firstname' => 'ASC',
                            'lastname' => 'ASC',
                            'compartment' => 'ASC',
                            'branch' => 'ASC'
                        )
                    );
            }
        }

        return
            $this->render(
                'TltAdmnBundle:Announcer:index.html.twig',
                array(
                    'announcers' => $announcers,
                    'form' => $form->createView()
                )
            );
    }

    /**
     * @Route("/announcers/add", name="tlt_admin_announcers_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $announcer = new Announcer();
        $announcer->setActive(true);

        $form = $this->createForm(
            new AnnouncerType($this->container->get('security.context')),
            $announcer
        );

        $form = $form->handleRequest($request);

        if ($form->isValid()) {
            $user	=	$this->getUser();
            $announcer->setInsertedBy($user->getUsername());
            $announcer->setModifiedBy($user->getUsername());
            $announcer->setFromHost($this->container->get('request')->getClientIp());

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->persist($announcer);
            $em->flush();

            return $this->redirect($this->generateUrl('tlt_admin_announcers_success', array('action'=>'add')));
        }

        return $this->render('TltAdmnBundle:Announcer:add.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/announcers/edit/{id}", name="tlt_admin_announcers_edit")
     * @Template("TltAdmnBundle:Branch:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $announcer = $this->getDoctrine()
            ->getRepository('TltAdmnBundle:Announcer')
            ->find($id);

        $form = $this->createForm( new AnnouncerType($this->get('security.context')), $announcer);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user	=	$this->getUser();
            $announcer->setModifiedBy($user->getUsername());
            $announcer->setFromHost($this->container->get('request')->getClientIp());

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('tlt_admin_announcers_success', array('action'=>'edit')));
        }

        return $this->render('TltAdmnBundle:Announcer:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/announcers/success/{action}", name="tlt_admin_announcers_success")
     * @Template()
     */
    public function successAction($action)
    {
        return $this->render('TltAdmnBundle:Announcer:success.html.twig', array('action'=>$action));
    }

    /**
     * @Route("/announcers/new", name="tlt_admin_announcers_new")
     */
    public function newAction(Request $request)
    {
        $announcer = new Announcer();
        $form = $this->createCreateForm($announcer);

        return $this->render(
            'TltAdmnBundle:Announcer:create.html.twig',
            array(
                'entity' => $announcer,
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/announcers/create", name="tlt_admin_announcers_create")
     */
    public function createAction(Request $request)
    {
        $announcer = new Announcer();

        $form = $this->createCreateForm($announcer);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user	=	$this->getUser();
            $announcer->setInsertedBy($user->getUsername());
            $announcer->setModifiedBy($user->getUsername());
            $announcer->setFromHost($this->container->get('request')->getClientIp());

            $em = $this->getDoctrine()->getManager();
            $em->persist($announcer);
            $em->flush();

            $message = 'Persoana <strong>' . $announcer->getFirstname() . ' ' . $announcer->getLastname() . '</strong> a fost adaugata cu succes la Agentia/Centrul <strong>' . $announcer->getBranch()->getName() . '</strong>, in compartimentul <strong>' . $announcer->getCompartment() . '</strong>';

            return new JsonResponse(
                array(
                    'message' => $message
                ),
                200
            );
        }

        $response = new JsonResponse(
            array(
                'message' => 'Error',
                'form' => $this->renderView(
                    'TltAdmnBundle:Announcer:create.html.twig',
                    array(
                        'entity' => $announcer,
                        'form' => $form->createView(),
                    ))), 400);

        return $response;
    }

    /**
     * Creates a form to create an Announcer entity.
     *
     * @param Announcer $entity The entity
     *
     * @return SymfonyComponentFormForm The form
     */
    private function createCreateForm(Announcer $entity)
    {
        $form = $this->createForm(new AnnouncerType($this->container->get('security.context')),
            $entity,
            array(
                'action' => $this->generateUrl('tlt_admin_announcers_create'),
                'method' => 'POST',
            ));

        return $form;
    }
}