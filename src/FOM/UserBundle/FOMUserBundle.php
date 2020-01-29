<?php

namespace FOM\UserBundle;

use FOM\UserBundle\DependencyInjection\Compiler\ForwardUserEntityClassPass;
use Mapbender\ManagerBundle\Component\Menu\MenuItem;
use Mapbender\ManagerBundle\Component\Menu\RegisterMenuRoutesPass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FOM\UserBundle\DependencyInjection\Factory\SspiFactory;
use Mapbender\ManagerBundle\Component\ManagerBundle;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        if (class_exists('\Mapbender\ManagerBundle\Component\Menu\MenuItem')) {
            // Mapbender >= 3.0.8.2
            $this->addMenu($container);
        }
        $container->addCompilerPass(new ForwardUserEntityClassPass('fom.user_entity', 'FOM\UserBundle\Entity\User'));
    }

    protected function addMenu(ContainerBuilder $container)
    {
        $userItem = MenuItem::create('fom.user.userbundle.users', 'fom_user_user_index')
            ->setWeight(100)
            ->addChildren(array(
                MenuItem::create('fom.user.userbundle.new_user', 'fom_user_user_create')
                    ->requireEntityGrant('FOM\UserBundle\Entity\User', 'CREATE'),
            ))
        ;
        $groupItem = MenuItem::create('fom.user.userbundle.groups', 'fom_user_group_index')
            ->setWeight(110)
            ->requireEntityGrant('FOM\UserBundle\Entity\Group', 'VIEW')
            ->addChildren(array(
                MenuItem::create('fom.user.userbundle.new_group', 'fom_user_group_create')
                    ->requireEntityGrant('FOM\UserBundle\Entity\Group', 'CREATE'),
            ))
        ;
        $aclItem = MenuItem::create('fom.user.userbundle.acls', 'fom_user_acl_index')
            ->setWeight(120)
            ->requireEntityGrant('Symfony\Component\Security\Acl\Domain\Acl', 'EDIT')
        ;
        $container->addCompilerPass(new RegisterMenuRoutesPass($userItem));
        $container->addCompilerPass(new RegisterMenuRoutesPass($groupItem));
        $container->addCompilerPass(new RegisterMenuRoutesPass($aclItem));
    }

    /**
     * @inheritdoc
     * @deprecated remove in FOM v3.3, require Mapbender >=3.0.8.2
     */
    public function getManagerControllers()
    {
        if (class_exists('\Mapbender\ManagerBundle\Component\Menu\MenuItem')) {
            // Mapbender >= 3.0.8.2
            return array();
        }
        return array(
            array(
                'title' => "fom.user.userbundle.users",
                'route'=>'fom_user_user_index',
                'weight' => 100,
                'subroutes' => array(
                   array(
                        'title' => "fom.user.userbundle.new_user",
                        'route'=>'fom_user_user_create',
                        'enabled' => function($securityContext) {
                            /** @var AuthorizationCheckerInterface $securityContext */
                            $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\User');
                            return $securityContext->isGranted('CREATE', $oid);
                        },
                    )
                ),
            ),
            array(
                'title' => "fom.user.userbundle.groups",
                'route'=>'fom_user_group_index',
                'weight' => 110,
                'enabled' => function($securityContext) {
                    /** @var AuthorizationCheckerInterface $securityContext */
                    $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group');
                    return $securityContext->isGranted('VIEW', $oid);
                },
                'subroutes' => array(
                    array(
                        'title' => "fom.user.userbundle.new_group",
                        'route'=>'fom_user_group_create',
                        'enabled' => function($securityContext) {
                            /** @var AuthorizationCheckerInterface $securityContext */
                            $oid = new ObjectIdentity('class', 'FOM\UserBundle\Entity\Group');
                            return $securityContext->isGranted('CREATE', $oid);
                        },
                    ),
                ),
            ),
            array(
                'title' => "fom.user.userbundle.acls",
                'route'=>'fom_user_acl_index',
                'weight' => 120,
                'enabled' => function($securityContext) {
                    /** @var AuthorizationCheckerInterface $securityContext */
                    $oid = new ObjectIdentity('class', 'Symfony\Component\Security\Acl\Domain\Acl');
                    return $securityContext->isGranted('EDIT', $oid);
                },
            ),
        );
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
