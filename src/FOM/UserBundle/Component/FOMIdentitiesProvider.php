<?php

namespace FOM\UserBundle\Component;

use Doctrine\ORM\EntityRepository;
use FOM\UserBundle\Component\Ldap;
use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides user and group identities available for ACL assignments
 * Service registered as fom.identities.provider
 */
class FOMIdentitiesProvider implements IdentitiesProviderInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var Ldap\UserProvider */
    protected $ldapUserIdentitiesProvider;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->ldapUserIdentitiesProvider = $container->get('fom.ldap_user_identities_provider');
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    /**
     * @param $entityName
     * @return EntityRepository
     */
    protected function getRepository($entityName)
    {
        return $this->getDoctrine()->getRepository($entityName);
    }

    /**
     * @return Group[]
     */
    public function getAllGroups()
    {
        $repo = $this->getRepository('FOMUserBundle:Group');
        return $repo->findAll();
    }

    public function getLdapUsers()
    {
        return $this->ldapUserIdentitiesProvider->getIdentities('*');
    }

    /**
     * @return User[]
     */
    public function getDatabaseUsers()
    {
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        return $repo->findAll();
    }

    public function getAllUsers()
    {
        return array_merge($this->getLdapUsers(), $this->getDatabaseUsers());
    }
}
