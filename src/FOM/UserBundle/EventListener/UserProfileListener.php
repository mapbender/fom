<?php

namespace FOM\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

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
            'loadClassMetadata'
        );
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $metadata = $args->getClassMetadata();
        $user = 'FOM\UserBundle\Entity\User';
        $profile = $this->container->getParameter('fom_user.profile_entity');

        if($profile !== $metadata->getName()) return;

        if($user == $metadata->getName()) {
            $metadata->mapOneToOne(array(
                'targetEntity' => $profile,
                'mappedBy' => 'uid'
            ));
        }

        if($profile == $metadata->getName()) {
            $metadata->setIdentifier(array('uid'));
            $metadata->mapOneToOne(array(
                'fieldName' => 'uid',
                'targetEntity' => $user,
                'inversedBy' => 'profile',
            ));
        }
    }
}
