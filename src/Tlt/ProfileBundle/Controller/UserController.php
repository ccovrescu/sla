<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 2/26/2015
 * Time: 8:27 AM
 */

namespace Tlt\ProfileBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Tlt\ProfileBundle\Entity\User;
use Tlt\ProfileBundle\Form\Model\ChangePassword;
use Tlt\ProfileBundle\Form\Type\ChangePasswordType;
use Tlt\ProfileBundle\Form\Type\UserType;

class UserController extends Controller
{
    /**
     * @Route("/user/index", name="profile_user_index")
     * @Template("TltProfileBundle:User:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $users = $this->getDoctrine()
            ->getRepository('TltProfileBundle:User')
            ->findAll();

        return array('users' => $users);
    }

    /**
     * @Route("/user/edit/{id}", name="profile_user_edit")
     * @Template("TltProfileBundle:User:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
//        $user = $this->getUser();
        $user = $this->getDoctrine()
            ->getRepository('TltProfileBundle:User')
            ->findOneById($id);

        $form = $this->createForm( new UserType(), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('profile_user_success', array('action'=>'edit')));
        }

        return $this->render('TltProfileBundle:User:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/user/chpwd", name="profile_user_chpwd")
     * @Template("TltProfileBundle:User:changePassword.html.twig")
     */
    public function changePassword(Request $request)
    {
        $user = $this->getUser();

//        $form = $this->createForm( new ChangePasswordType(), $user);

        $changePasswordModel = new ChangePassword();
        $form = $this->createForm(new ChangePasswordType(), $changePasswordModel);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setPassword($changePasswordModel->getNewPassword());

            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('profile_user_success', array('action'=>'chpwd')));
        }

        return array(
            'form' => $form->createView(),
        );
    }
    /**
     * @Route("/user/success/{action}", name="profile_user_success")
     * @Template("TltProfileBundle:User:success.html.twig")
     */
    public function successAction($action)
    {
        return array('action'=>$action);
    }

    /**
     * @Route("/user/details", name="profile_user_details")
     * @Template("TltProfileBundle:User:details.html.twig")
     */
    public function detailsAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm( new UserType(), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirect($this->generateUrl('profile_user_success', array('action'=>'edit')));
        }

        return $this->render('TltProfileBundle:User:details.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}