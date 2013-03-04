<?php

namespace FOM\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use FOM\UserBundle\DependencyInjection\Factory\LdapSecurityFactory;
use FOM\ManagerBundle\Component\ManagerBundle;

/**
 * FOMUserBundle - provides user management
 *
 * @author Christian Wygoda
 */
class FOMUserBundle extends ManagerBundle
{
    public function build(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new LdapSecurityFactory());
    }

    /**
     * @inheritdoc
     */
    public function getManagerControllers()
    {
        return array(
            array(
                'title' => 'User Control',
                'weight' => 100,
                'route' => 'fom_user_user_index',
                'routes' => array(
                    'fom_user_user',
                    'fom_user_group',
                    'fom_user_acl'
                ),
                'subroutes' => array(
                    0 => array('title'=>'Users',
                               'route'=>'fom_user_user_index',
                               'subroutes' => array(
                                    0 => array('title'=>'New User',
                                               'route'=>'fom_user_user_new')
                                )
                              ),
                    1 => array('title'=>'Groups', 
                               'route'=>'fom_user_group_index',
                               'subroutes' => array(
                                    0 => array('title'=>'New Group',
                                               'route'=>'fom_user_group_new')
                                )
                               ),
                    2 => array('title'=>'ACLs',
                               'route'=>'fom_user_acl_index')
                )
            )
        );
    }

    public function getRoles()
    {
        return array(
            'ROLE_SUPER_ADMIN' => 'Can administrate everything (super admin)',
            'ROLE_USER_ADMIN' => 'Can administrate users & groups');
    }

    public function getACLClasses()
    {
        return array(
            'FOM\UserBundle\Entity\User' => 'Users',
            'FOM\UserBundle\Entity\Group' => 'Groups');
    }
}
