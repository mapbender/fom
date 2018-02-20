<?php

namespace FOM\UserBundle\Component;

use FOM\ManagerBundle\Component\ManagerBundle;
use Symfony\Component\HttpKernel\Kernel;

class RolesService
{
    /** @var Kernel */
    protected $kernel;
    /** @var string[] */
    protected $roles;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    protected function loadRoles()
    {
        $this->roles = array();
        foreach($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof ManagerBundle) {
                $bundle_roles = $bundle->getRoles();
                $this->roles = array_merge($this->roles, $bundle_roles);
            }
        }
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        if($this->roles === null) {
            $this->loadRoles();
        }

        return $this->roles;
    }
}