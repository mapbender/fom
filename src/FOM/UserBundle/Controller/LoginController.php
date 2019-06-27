<?php
namespace FOM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * User controller.
 *
 * @author Christian Wygoda
 * @author Paul Schmidt
 */
class LoginController extends Controller
{
    /**
     * User login
     *
     * @Route("/user/login", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        return $this->render('@FOMUser/Login/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'selfregister' => $this->container->getParameter("fom_user.selfregister"),
            'reset_password' => $this->container->getParameter("fom_user.reset_password"),
        ));
    }

    /**
     * @Route("/user/login/check")
     */
    public function loginCheckAction()
    {
        //Don't worry, this is actually intercepted by the security layer.
    }

    /**
     * @Route("/user/logout")
     */
    public function logoutAction()
    {
        //Don't worry, this is actually intercepted by the security layer.
    }
}
