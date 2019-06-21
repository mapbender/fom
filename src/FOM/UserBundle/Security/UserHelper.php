<?php

namespace FOM\UserBundle\Security;

use FOM\UserBundle\Component\UserHelperService;
use FOM\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper for user related stuff
 *
 * @author Christian Wygoda
 * @deprecated use the service fom.user_helper.service; remove in FOM v3.3
 */
class UserHelper
{
    /** @var UserHelperService */
    protected $service;

    /**
     * UserHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->service = $container->get('fom.user_helper.service');
    }

    /**
     * Set salt, encrypt password and set it on the user object
     *
     * @param User $user User object to manipulate
     * @param string $password Password to encrypt and store
     */
    public function setPassword(User $user, $password)
    {
        return $this->service->setPassword($user, $password);
    }

    /**
     * Gives a user the right to edit himself.
     */
    public function giveOwnRights($user)
    {
        return $this->service->giveOwnRights($user);
    }
}
