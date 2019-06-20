<?php

namespace FOM\UserBundle\Controller;

use FOM\UserBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\UserRegistrationType;

/**
 * Self registration controller.
 *
 * @author Christian Wygoda
 * @author Paul Schmidt
 */

class RegistrationController extends UserControllerBase
{
    /**
     * Check if self registration is allowed.
     *
     * setContainer is called after controller creation is used to deny access to controller if self registration has
     * been disabled.
     * @param ContainerInterface $container
     * @throws AccessDeniedHttpException
     */
    public function setContainer(ContainerInterface $container = NULL)
    {
        parent::setContainer($container);

        if(false && !$this->container->getParameter('fom_user.selfregister'))
            throw new AccessDeniedHttpException();
    }

    /**
     * Registration step 3: Show instruction page that email has been sent
     *
     * @Route("/user/registration/send", methods={"GET"})
     * @return Response
     */
    public function sendAction()
    {
        return $this->render('@FOMUser/Registration/send.html.twig');
    }

    /**
     * Registration step 1: Registration form
     *
     * @Route("/user/registration", methods={"GET"})
     * @return Response
     */
    public function formAction()
    {
        $user = new User();
        $form = $this->createForm(new UserRegistrationType(), $user);

        return $this->render('@FOMUser/Registration/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
        ));
    }

    /**
     * Registration step 2: Create user and set registration token
     *
     * @Route("/user/registration", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this
            ->createForm(new UserRegistrationType(), $user)
            ->handleRequest($request)
        ;

        //@TODO: Check if username and email are unique

        if($form->isSubmitted() && $form->isValid()) {
            $helperService = $this->getUserHelper();
            $helperService->setPassword($user, $user->getPassword());

            $user->setRegistrationToken(hash("sha1",rand()));
            $user->setRegistrationTime(new \DateTime());

            $groupRepository = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
            foreach($this->container->getParameter('fom_user.self_registration_groups') as $groupTitle) {
                /** @var Group|null $group */
                $group = $groupRepository->findOneBy(array(
                    'title' => $groupTitle,
                ));
                if ($group) {
                    $user->addGroup($group);
                } else {
                    $msg = sprintf('Self-registration group "%s" not found for user "%s"',
                        $groupTitle,
                        $user->getUsername());
                    /** @var LoggerInterface $logger */
                    $logger = $this->get('logger');
                    $logger->error($msg);
                }

            }

            $this->sendEmail($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $helperService->giveOwnRights($user);

            return $this->redirectToRoute('fom_user_registration_send');
        }

        return $this->render('@FOMUser/Registration/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'form_name' => $form->getName(),
        ));
    }

    /**
     * Registration step 4: Activate account by token
     *
     * @Route("/user/activate", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function confirmAction(Request $request)
    {
        $user = $this->getUserFromRegistrationToken($request);
        if(!$user) {
            return $this->render('@FOMUser/Login/error-notoken.html.twig', array(
                'site_email' => $this->getEmailFromAdress(),
            ));
        }

        /** @var User $user */
        // Check token age
        $max_registration_age = $this->container->getParameter("fom_user.max_registration_time");
        if(!$this->checkTimeInterval($user->getRegistrationTime(), $max_registration_age)) {
            $form = $this->createForm('form');
            return $this->render('@FOMUser/Login/error-tokenexpired.html.twig', array(
                'user' => $user,
                'form' => $form->createView()
            ));
        }

        // Unset token
        $em = $this->getDoctrine()->getManager();
        $user->setRegistrationToken(null);
        $em->flush();

        // Forward to final page
        return $this->redirect($this->generateUrl('fom_user_registration_done'));
    }

    /**
     * Registration step 4a: Reset token (if expired)
     *
     * @Route("/user/registration/reset", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function resetAction(Request $request)
    {
        $user = $this->getUserFromRegistrationToken($request);
        if(!$user) {
            return $this->render('@FOMUser/Login/error-notoken.html.twig', array(
                'site_email' => $this->getEmailFromAdress(),
            ));
        }

        $user->setRegistrationToken(hash("sha1",rand()));
        $user->setRegistrationTime(new \DateTime());

        $this->sendEmail($user);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('fom_user_registration_send'));
    }

    /**
     * Registration step 5: Welcome new user
     *
     * @Route("/user/registration/done", methods={"GET"})
     * @return Response
     */
    public function doneAction()
    {
        return $this->render('@FOMUser/Registration/done.html.twig');
    }

    /**
     * @param User $user
     */
    protected function sendEmail($user)
    {
       $fromName = $this->container->getParameter("fom_user.mail_from_name");
       $fromEmail = $this->getEmailFromAdress();
       $mailFrom = array($fromEmail => $fromName);
       /** @var \Swift_Mailer $mailer */
       $mailer = $this->get('mailer');
       $text = $this->renderView('FOMUserBundle:Registration:email-body.text.twig', array("user" => $user));
       $html = $this->renderView('FOMUserBundle:Registration:email-body.html.twig', array("user" => $user));
       $message = \Swift_Message::newInstance()
           ->setSubject($this->renderView('FOMUserBundle:Registration:email-subject.text.twig'))
           ->setFrom($mailFrom)
           ->setTo($user->getEmail())
           ->setBody($text)
           ->addPart($html, 'text/html');

       $mailer->send($message);
    }

    /**
     * @param Request $request
     * @return User|null
     */
    protected function getUserFromRegistrationToken(Request $request)
    {
        $token = $request->get('token');
        if ($token) {
            /** @var User|null $user */
            $user = $this->getDoctrine()->getRepository("FOMUserBundle:User")->findOneBy(array(
                'resetToken' => $token,
            ));
            return $user;
        } else {
            return null;
        }
    }
}
