<?php

namespace FOM\UserBundle\Component;

use FOM\UserBundle\Entity\Group;

interface IdentitiesProviderInterface
{
    /**
     * Get user security identifiers for given query.
     *
     * @param  string $search Query string
     * @return string[]
     */
    public function getUsers($search);


    /**
     * Get role identifiers
     *
     * @return string[]
     */
    public function getRoles();

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
