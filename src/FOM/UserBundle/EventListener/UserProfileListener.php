<?php

namespace FOM\UserBundle\EventListener;

use FOM\UserBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Event listener for adding user profile on the fly
 *
 * @author Christian Wygoda
 */
class UserProfileListener implements EventSubscriber
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postLoad',
        );
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();

        if(!$user instanceof User) return;

        $em = $args->getEntityManager();
        $profileEntity = $this->container->getParameter('fom_user.profile_entity');

        if($user->getId() && $profileEntity !== null) {
            $profile = $this->container->get('doctrine')->getRepository($profileEntity)
                ->find($user->getId());
            if(!$profile) {
                $profile = new $profileEntity();
            }
            $user->setProfile($profile);
        }
    }
}
