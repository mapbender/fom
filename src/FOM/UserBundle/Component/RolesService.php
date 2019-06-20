<?php

namespace FOM\UserBundle\Component;

/**
 * Legacy artifact. In container as 'fom_roles'
 * @deprecated remove in FOM v3.3
 */
class RolesService
{
    /**
     * @return string[]
     */
    protected function getRoles()
    {
        return array();
    }

    /**
     * Get roles
     *
     * @return string[]
     */
    public function getAll()
    {
        return array();
    }
}