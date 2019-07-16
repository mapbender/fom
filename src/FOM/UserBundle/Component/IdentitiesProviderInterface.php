<?php

namespace FOM\UserBundle\Component;

use FOM\UserBundle\Entity\Group;

interface IdentitiesProviderInterface
{
    /**
     * Get all group objects
     * @return Group[]
     */
    public function getAllGroups();

    /**
     * Get all user objects
     * @return object[]
     */
    public function getAllUsers();
}
