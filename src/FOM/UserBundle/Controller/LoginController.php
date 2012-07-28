<?php

/**
 * TODO: License
 */

namespace FOM\UserBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

use FOM\UserBundle\Entity\User;
use FOM\UserBundle\Form\Type\UserType;

/**
 * User controller.
 *
 * @author Christian Wygoda
 * @author Paul Schmidt
 */
class LoginController extends Controller {
    /**
     * User login
     *
     * @Route("/user/login")
     * @Template()
     * @Method("GET")
     */
    public function loginAction() {
        $request = $this->get('request');
        if($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
            'selfregister' => $this->container->getParameter("fom_user.selfregister")
        );
    }

    /**
     * @Route("/user/login/check")
     */
    public function loginCheckAction() {
        //Don't worry, this is actually intercepted by the security layer.
    }

    /**
     * @Route("/user/logout")
     */
    public function logoutAction() {
        //Don't worry, this is actually intercepted by the security layer.
    }
}

