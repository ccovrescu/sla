<?php

namespace Tlt\AdmnBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Tlt\AdmnBundle\Entity\Choose;
use Tlt\AdmnBundle\Entity\SystemCategory;
use Tlt\AdmnBundle\Form\SystemCategoryType;
use Tlt\AdmnBundle\Form\Type\ChooseType;

/**
 * SystemCategory controller.
*
* @Route("/systemcategory")
*/
class SystemCategoryController extends Controller
{

    /**
     * Lists all SystemCategory entities.
     *
     * @Route("/index", name="admin_systemcategory_index")
     * @Template("TltAdmnBundle:SystemCategory:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(
            ChooseType::class,
            new Choose(),
            array(
                'department' => array(
                    'available'=>true,
                    'showAll' => true,
                ),
                'doctrine'=> $this->getDoctrine(),
            )
        );

        $form->handleRequest($request);

        $categories = null;

        if ($form->isValid()) {
            if ($form['department']->getData()!=0) {
                $categories = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:SystemCategory')
                    ->findAllFromOneDepartmentOrderedByName($form['department']->getData());
            } else {
                $categories = $this->getDoctrine()
                    ->getRepository('TltAdmnBundle:SystemCategory')
                    ->findAllOrderedByName();
            }
        }

        return $this->render(
            'TltAdmnBundle:SystemCategory:index.html.twig',
            array(
                'form' => $form->createView(),
                'categories' => $categories
            )
        );
    }


    /**
     * Lists all SystemCategory entities.
     *
     * @Route("/index1", name="admin_systemcategory_index1")
//     * @Method("GET")
     * @Template("TltAdmnBundle:SystemCategory:index.html.twig")
     */
    public function indexAction1()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TltAdmnBundle:SystemCategory')->findAll();
        //var_dump($entities);die();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new SystemCategory entity.
     *
     * @Route("/", name="admin_systemcategory_create")
     * @Template("TltAdmnBundle:SystemCategory:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new SystemCategory();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_systemcategory_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SystemCategory entity.
     *
     * @param SystemCategory $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SystemCategory $entity)
    {
        $form = $this->createForm(SystemCategoryType::class, $entity, array(
            'action' => $this->generateUrl('admin_systemcategory_create'),
            'method' => 'POST',
        ));

        $form->add('salveaza', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Validare'));

        return $form;
    }

    /**
     * Displays a form to create a new SystemCategory entity.
     *
     * @Route("/new", name="admin_systemcategory_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new SystemCategory();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a SystemCategory entity.
     *
     * @Route("/{id}", name="admin_systemcategory_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TltAdmnBundle:SystemCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SystemCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing SystemCategory entity.
     *
     * @Route("/{id}/edit", name="admin_systemcategory_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TltAdmnBundle:SystemCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SystemCategory entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a SystemCategory entity.
    *
    * @param SystemCategory $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(SystemCategory $entity)
    {
        $form = $this->createForm(SystemCategoryType::class, $entity, array(
            'action' => $this->generateUrl('admin_systemcategory_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('salveaza', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salvare'));

        return $form;
    }
    /**
     * Edits an existing SystemCategory entity.
     *
     * @Route("/{id}", name="admin_systemcategory_update")
     * @Method("PUT")
     * @Template("TltAdmnBundle:SystemCategory:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TltAdmnBundle:SystemCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SystemCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_systemcategory_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a SystemCategory entity.
     *
     * @Route("/{id}", name="admin_systemcategory_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TltAdmnBundle:SystemCategory')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SystemCategory entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_systemcategory_index'));
    }

    /**
     * Creates a form to delete a SystemCategory entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_systemcategory_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('salveaza', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Sterge'))
            ->getForm()
        ;
    }
}
