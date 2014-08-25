<?php

namespace FOM\UserBundle\EventListener;

use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use FOM\UserBundle\Entity\User;


/**
 * Event listener for failed logins which upscales forced wait time.
 *
 * @author Christian Wygoda
 */
class FailedLoginListener
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onLoginSuccess(AuthenticationEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if(!($user instanceof User)) return;

        if($user->isAccountNonLocked()) {
            $user->setLoginFailCount(null);
            $user->setLoginFailed(null);

            $this->container->get('doctrine')->getManager()->flush();
        }
    }

    public function onLoginFailure(AuthenticationFailureEvent $event)
    {
        $username = $this->container->get('request')->get('_username');
        $doctrine = $this->container->get('doctrine');
        $em = $doctrine->getManager();
        $repository = $doctrine->getRepository('FOMUserBundle:User');
        $user = $repository->findOneByUsername($username);

        if(!($user instanceof User)) return;

        if($user) {

            $failCount = $user->getLoginFailCount();

            $user->setLoginFailCount($failCount ? $failCount + 1 : 1);
            $user->setLoginFailed(new \DateTime());

            $em->flush();
        }
    }
}
