<?php

namespace FOM\UserBundle\Component;

use FOM\ManagerBundle\Component\ManagerBundle;
use Mapbender\CoreBundle\MapbenderCoreBundle;

/**
 * Roles service class
 * registered as "fom_roles" symfony service
 * in FOM/UserBundle/Resources/config/services.xml
 *
 * @package FOM\UserBundle\Component
 */
class RolesService
{
    protected $kernel;

    /**
     * RolesService constructor.
     *
     * @param \AppKernel $kernel
     */
    public function __construct(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return MapbenderCoreBundle[]|ManagerBundle[]|null
     */
    protected function getRoles()
    {
        /** @var ManagerBundle $bundle */
        $roles = array();
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!is_subclass_of($bundle, 'FOM\ManagerBundle\Component\ManagerBundle')) {
                continue;
            }
            $roles = array_merge($roles, $bundle->getRoles());
        }
        return $roles;
    }

    /**
     * Get roles
     *
     * @return MapbenderCoreBundle[]|ManagerBundle[]|null
     */

    public function getAll()
    {
        static $roles;

        if ($roles === null) {
            $roles = $this->getRoles();
        }

        return $roles;
    }
}