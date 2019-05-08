<?php
namespace FOM\UserBundle\Controller;

use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\UserForgotPassType;
use FOM\UserBundle\Form\Type\UserResetPassType;
use FOM\UserBundle\Security\UserHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Password reset controller.
 *
 * Workflow is as follows:
 *   1) GET fom_user_password_form /password/forgot form.html.twig
 *      Form with username / email field for lookup
 *   2) POST fom_user_password_request /password/forgot form.html.twig
 *      Lookup user, set form error if not found or not activated
 *      Set reset token
 *      Send email
 *      Forward to send page
 *   3) GET fom_user_password_send /password/send send.html.twig
 *      Show instructions
 *   4) GET fom_user_password_resetform /password/reset/{token} resetform.html.twig, resetform-error-*.html.twig
 *      Lookup token, show error if not found or expired
 *      Show form with password field
 *   5) POST fom_user_password_reset /password/reset/{token} reset-error-*.html.twig
 *      Lookup token, show error if not found or expired
 *      Set new password
 *      Remove reset token
 *      Forward to confirm page
 *   6) GET fom_user_password_done /password/done done.html.twig
 *      Show congratulation page with link to login
 *
 * @author Christian Wygoda
 * @author Paul Schmidt
 */
class PasswordController extends Controller
{
    /**
     * Check if password reset is allowed.
     *
     * setContainer is called after controller creation is used to deny access to controller if password reset has
     * been disabled.
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        if (!$this->container->getParameter('fom_user.reset_password')) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Password reset step 3: Show instruction page that email has been sent
     *
     * @Route("/user/password/send", methods={"GET"})
     * @return Response
     */
    public function sendAction()
    {
        return $this->render('@FOMUser/Password/send.html.twig');
    }

    /**
     * Password reset step 1: Request reset token
     *
     * @Route("/user/password", methods={"GET"})
     */
    public function formAction()
    {
        $form = $this->createForm(new UserForgotPassType());
        return $this->render('@FOMUser/Password/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Password reset step 2: Create reset token and send email
     *
     * @Route("/user/password", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function requestAction(Request $request)
    {
        $form = $this
            ->createForm(new UserForgotPassType())
            ->handleRequest($request)
        ;

        $obj = $form->getData();
        $userRepository = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        /** @var User|null $user */
        $user = $userRepository->findOneBy(array(
            'userName' => $obj['search'],
        ));
        if (!$user) {
            $user = $userRepository->findOneBy(array(
                'email' => $obj['search'],
            ));
        }

        if (!$user) {
            $message = $this->renderView('FOMUserBundle:Password:request-error-nosuchuser.html.twig');
            $form->addError(new FormError($message));
            return $this->render('@FOMUser/Password/form.html.twig', array(
                'form' => $form->createView(),
            ));
        } elseif ($user->getRegistrationToken()) {
            $message = $this->renderView('FOMUserBundle:Password:request-error-userinactive.html.twig');
            $form->addError(new FormError($message));
            return $this->render('@FOMUser/Password/form.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        $this->setResetToken($user);

        return $this->redirect($this->generateUrl('fom_user_password_send'));
    }

    /**
     * Password reset step 4a: Reset the reset token (pun intended...)
     *
     * @Route("/user/reset/reset", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function tokenResetAction(Request $request)
    {
        $token = $request->get('token');
        if (!$token) {
            return $this->render('FOMUserBundle:Login:error-notoken.html.twig');
        }
        /** @var User|null $user */
        $user = $this->getDoctrine()->getRepository("FOMUserBundle:User")->findOneBy(array(
            'resetToken' => $token,
        ));
        if (!$user) {
            $mail = $this->container->getParameter('fom_user.mail_from_address');
            return $this->render('FOMUserBundle:Login:error-notoken.html.twig', array(
                'site_email' => $mail));
        }

        $this->setResetToken($user);

        return $this->redirect($this->generateUrl('fom_user_password_send'));
    }

    /**
     * Password reset step 4: Show password reset form
     *
     * @Route("/user/reset", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function resetAction(Request $request)
    {
        $token = $request->get('token');
        if (!$token) {
            return $this->render('FOMUserBundle:Login:error-notoken.html.twig');
        }

        $user = $this->getDoctrine()->getRepository("FOMUserBundle:User")->findOneBy(array(
            'resetToken' => $token,
        ));
        if (!$user) {
            $mail = $this->container->getParameter('fom_user.mail_from_address');
            return $this->render('FOMUserBundle:Login:error-notoken.html.twig', array(
                'site_email' => $mail));
        }

        $max_token_age = $this->container->getParameter("fom_user.max_reset_time");
        if (!$this->checkTimeInterval($user->getResetTime(), $max_token_age)) {
            $form = $this->createForm('form');
            return $this->render('FOMUserBundle:Login:error-tokenexpired.html.twig', array(
                'user' => $user,
                'form' => $form->createView()
            ));
        }

        $form = $this->createForm(new UserResetPassType(), $user);
        return $this->render('@FOMUser/Password/reset.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Password reset step 5: reset password
     *
     * @Route("/user/reset", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function passwordAction(Request $request)
    {
        $token = $request->get('token');
        if (!$token) {
            return $this->render('FOMUserBundle:Login:error-notoken.html.twig');
        }

        /** @var User|null $user */
        $user = $this->getDoctrine()->getRepository("FOMUserBundle:User")->findOneBy(array(
            'resetToken' => $token,
        ));
        if (!$user) {
            $mail = $this->container->getParameter('fom_user.mail_from_address');
            return $this->render('FOMUserBundle:Login:error-notoken.html.twig', array(
                'site_email' => $mail));
        }

        $max_token_age = $this->container->getParameter("fom_user.max_reset_time");
        if (!$this->checkTimeInterval($user->getResetTime(), $max_token_age)) {
            $form = $this->createForm('form');
            return $this->render('FOMUserBundle:Login:error-tokenexpired.html.twig', array(
                'user' => $user,
                'form' => $form->createView()
            ));
        }

        $form = $this->createForm(new UserResetPassType(), $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setResetToken(null);

            $helper = new UserHelper($this->container);
            $helper->setPassword($user, $user->getPassword());

            $em->flush();

            return $this->redirect($this->generateUrl('fom_user_password_done'));
        }

        return $this->render('@FOMUser/Password/reset.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Password reset step 6: All done message
     *
     * @Route("/user/reset/done", methods={"GET"})
     * @return Response
     */
    public function doneAction()
    {
        return $this->render('@FOMUser/Password/done.html.twig');
    }

    protected function checkTimeInterval($startTime, $timeInterval)
    {
        $checktime = new \DateTime();
        $checktime->sub(new \DateInterval(sprintf("PT%dH", $timeInterval)));
        if ($startTime < $checktime) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    protected function setResetToken($user)
    {
        $user->setResetToken(hash("sha1", rand()));
        $user->setResetTime(new \DateTime());

        //send email
        $fromName = $this->container->getParameter("fom_user.mail_from_name");
        $fromEmail = $this->container->getParameter("fom_user.mail_from_address");
        $mailFrom = array($fromEmail => $fromName);
        $mailer = $this->get('mailer');

        $text = $this->get("templating")->render('FOMUserBundle:Password:email-body.text.twig', array("user" => $user));
        $html = $this->get("templating")->render('FOMUserBundle:Password:email-body.html.twig', array("user" => $user));
        $message = \Swift_Message::newInstance()
            ->setSubject($this->get("templating")->render('FOMUserBundle:Password:email-subject.text.twig'))
            ->setFrom($mailFrom)
            ->setTo($user->getEmail())
            ->setBody($text)
            ->addPart($html, 'text/html');
        $mailer->send($message);

        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
}
