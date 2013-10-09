<?php

namespace FOM\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use FOM\UserBundle\DependencyInjection\Factory\LdapSecurityFactory;
use FOM\UserBundle\DependencyInjection\Factory\SspiFactory;
use FOM\ManagerBundle\Component\ManagerBundle;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

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
        $extension->addSecurityListenerFactory(new SspiFactory());
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
                                    0 => array(
                                      'title'=>'New User',
                                      'route'=>'fom_user_user_new',
                                      'enabled' => function($securityContext) {
                                          $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\User');
                                          return $securityContext->isGranted('CREATE', $oid);
                                      })
                                )
                              ),
                    1 => array('title'=>'Groups',
                               'route'=>'fom_user_group_index',
                               'subroutes' => array(
                                    0 => array(
                                      'title'=>'New Group',
                                      'route'=>'fom_user_group_new',
                                      'enabled' => function($securityContext) {
                                          $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group');
                                          return $securityContext->isGranted('CREATE', $oid);
                                      })
                                )
                               ),
                    2 => array('title'=>'ACLs',
                               'route'=>'fom_user_acl_index',
                                'enabled' => function($securityContext) {
                                    $oid = new ObjectIdentity('class', 'Symfony\Component\Security\Acl\Domain\Acl');
                                    return $securityContext->isGranted('CREATE', $oid) || $securityContext->isGranted('EDIT', $oid);
                                })
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
            'Symfony\Component\Security\Acl\Domain\Acl' => 'ACLs',
            'FOM\UserBundle\Entity\User' => 'Users',
            'FOM\UserBundle\Entity\Group' => 'Groups');
    }
}
