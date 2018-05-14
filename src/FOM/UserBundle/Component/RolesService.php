<?php

namespace FOM\UserBundle\Component;

use FOM\ManagerBundle\Component\ManagerBundle;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Roles service class
 * registered as "fom_roles" symfony service
 * in FOM/UserBundle/Resources/config/services.xml
 *
 * @package FOM\UserBundle\Component
 */
class RolesService
{
    /** @var Kernel */
    protected $kernel;

    /** @var string[] */
    protected $roles;

    /**
     * RolesService constructor.
     *
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return string[]
     */
    protected function getRoles()
    {
        $roles = array();
        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof ManagerBundle) {
                $bundle_roles = $bundle->getRoles();
                $roles = array_merge($roles, $bundle_roles);
            }
        }
        return $roles;
    }

    /**
     * Get roles
     *
     * @return string[]
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