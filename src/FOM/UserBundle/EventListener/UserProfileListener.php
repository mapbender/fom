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

        if($user == $metadata->getName()) {
            $metadata->mapOneToOne(array(
                'fieldName' => 'profile',
                'targetEntity' => $profile,
                'mappedBy' => 'uid',
                'cascade' => array('persist'),
            ));
        }

        if($profile == $metadata->getName()) {
            $metadata->setIdentifier(array('uid'));
            $metadata->mapOneToOne(array(
                'fieldName' => 'uid',
                'targetEntity' => $user,
                'inversedBy' => 'profile',
                'id' => true,
                'joinColumns' => array(array(
                    'name' => 'uid',
                    'referencedColumnName' => 'id'))
            ));
        }
    }
}
