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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Tlt\ProfileBundle\Entity\User;
use Tlt\ProfileBundle\Form\Type\ResetType;

class ResetController extends Controller
{
    const SESSION_EMAIL = 'tlt_profile_reset_email';

    /**
     * @Route("/reset-request", name="tlt_profile_reset_request")
     * @Method({"GET"})
     * @Template()
     */
    public function requestAction()
    {
        return array();
    }

    /**
     * Request reset user password
     *
     * @Route("/send-email", name="tlt_profile_reset_send_email")
     * @Method({"POST"})
     */
    public function sendEmailAction()
    {
        $username = $this->getRequest()->request->get('username');

        $emailConstraint = new EmailConstraint();
        $emailConstraint->message = 'Adresa de e-mail nu este valida';

        $errors = $this->get('validator')->validateValue(
            $username,
            $emailConstraint
        );

        if (count($errors)==0)
             $user = $this->getDoctrine()
                    ->getRepository('TltProfileBundle:User')
                    ->findOneBy(
                        array(
                            'email' => $username
                        )
                    );
        else
            $user = $this->getDoctrine()
                ->getRepository('TltProfileBundle:User')
                ->findOneBy(
                    array(
                        'username' => $username
                    )
                );

        if (null === $user) {
            return $this->render('TltProfileBundle:Reset:request.html.twig', array('invalid_username' => $username));
        }

        if ($user->isPasswordRequestNonExpired(86400)) {
            $this->get('session')->getFlashBag()->add(
                'warn',
                'Parola pentru acest utilizator a mai fost solicitata in ultimele 24 ore.'
            );

            return $this->redirect($this->generateUrl('tlt_profile_reset_request'));
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        $this->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));

        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
            ->setSubject('Resetare parola')
            ->setFrom('no-reply@teletrans.ro', 'Aplicatie SLA')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView('TltProfileBundle:Mail:reset.html.twig', array('user' => $user)),
                'text/html'
            );

        $user->setPasswordRequestedAt(new \DateTime('now', new \DateTimeZone('Europe/Bucharest')));

        $mailer->send($message);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirect($this->generateUrl('tlt_profile_reset_check_email'));
    }

    /**
     * Reset user password
     *
     * @Route("/reset/{token}", name="tlt_profile_reset_reset", requirements={"token"="\w+"})
     * @Method({"GET", "POST"})
     * @Template
     */
    public function resetAction(Request $request, $token)
    {
        $user = $this->getDoctrine()
            ->getRepository('TltProfileBundle:User')
            ->findOneBy(
                array(
                    'confirmationToken' => $token
                )
            );

        $session = $this->get('session');

        if (null === $user) {
            throw $this->createNotFoundException(
                sprintf('The user with "confirmation token" does not exist for value "%s"', $token)
            );
        }

        if (!$user->isPasswordRequestNonExpired(86400)) {
            $session->getFlashBag()->add(
                'warn',
                'The password for this user has already been requested within the last 24 hours.'
            );

            return $this->redirect($this->generateUrl('tlt_profile_reset_request'));
        }

        $form = $this->createForm( new ResetType(), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);

            $user
                ->setPlainPassword($form->getData()->getPlainPassword())
                ->setConfirmationToken(null)
                ->setPasswordRequestedAt(null)
                ->setStatus(true);

            $user
                ->setPassword($encoder->encodePassword($user->getPlainPassword(), $user->getSalt()));

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $session->getFlashBag()->add('success', 'Your password has been successfully reset. You may login now.');

            // force user logout
            $session->invalidate();
            $this->get('security.context')->setToken(null);

            return $this->redirect($this->generateUrl('tlt_profile_security_login'));
        }

        return array(
            'token' => $token,
            'form'  => $form->createView(),
        );
    }

    /**
     * Tell the user to check his email provider
     *
     * @Route("/check-email", name="tlt_profile_reset_check_email")
     * @Method({"GET"})
     * @Template
     */
    public function checkEmailAction()
    {
        $session = $this->get('session');
        $email = $session->get(static::SESSION_EMAIL);

        $session->remove(static::SESSION_EMAIL);

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return $this->redirect($this->generateUrl('tlt_profile_reset_request'));
        }

        return array(
            'email' => $email,
        );
    }

    /**
     * Get the truncated email displayed when requesting the resetting.
     * The default implementation only keeps the part following @ in the address.
     *
     * @param User $user
     *
     * @return string
     */
    protected function getObfuscatedEmail(User $user)
    {
        $email = $user->getEmail();

        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }
}
