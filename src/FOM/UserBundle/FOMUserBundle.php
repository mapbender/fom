<?php

namespace FOM\UserBundle;

use Mapbender\ManagerBundle\Component\Menu\MenuItem;
use Mapbender\ManagerBundle\Component\Menu\RegisterMenuRoutesPass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FOM\UserBundle\DependencyInjection\Factory\SspiFactory;
use Mapbender\ManagerBundle\Component\ManagerBundle;

/**
 * FOMUserBundle - provides user management
 *
 * @author Christian Wygoda
 */
class FOMUserBundle extends ManagerBundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SspiFactory());
        $this->addMenu($container);
    }

    protected function addMenu(ContainerBuilder $container)
    {
        $userItem = MenuItem::create('fom.user.userbundle.user_control', 'fom_user_user_index')
            ->setWeight(100)
            ->addChildren(array(
                MenuItem::create('fom.user.userbundle.users', 'fom_user_user_index')
                    ->addChildren(array(
                        MenuItem::create('fom.user.userbundle.new_user', 'fom_user_user_create')
                            ->requireEntityGrant('FOM\UserBundle\Entity\User', 'CREATE'),
                    )),
                MenuItem::create('fom.user.userbundle.groups', 'fom_user_group_index')
                    ->requireEntityGrant('FOM\UserBundle\Entity\Group', 'CREATE')
                    ->addChildren(array(
                        MenuItem::create('fom.user.userbundle.new_group', 'fom_user_group_create')
                            ->requireEntityGrant('FOM\UserBundle\Entity\Group', 'CREATE'),
                    )),
                MenuItem::create('fom.user.userbundle.acls', 'fom_user_acl_index')
                    ->requireEntityGrant('Symfony\Component\Security\Acl\Domain\Acl', array(
                        'CREATE',
                        'EDIT',
                    )),
            ))
        ;
        $container->addCompilerPass(new RegisterMenuRoutesPass($userItem));
    }

    public function getACLClasses()
    {
        $trans = $this->container->get('translator');
        return array(
            'Symfony\Component\Security\Acl\Domain\Acl' => $trans->trans("fom.user.userbundle.classes.acls"),
            'FOM\UserBundle\Entity\User' => $trans->trans("fom.user.userbundle.classes.users"),
            'FOM\UserBundle\Entity\Group' => $trans->trans("fom.user.userbundle.classes.groups"),
        );
    }
}
