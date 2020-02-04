<?php

namespace FOM\UserBundle\Component;

use Doctrine\ORM\EntityRepository;
use FOM\UserBundle\Component\Ldap;
use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

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
    /** @var string */
    protected $userEntityClass;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->ldapUserIdentitiesProvider = $container->get('fom.ldap_user_identities_provider');
        $this->userEntityClass = $container->getParameter('fom.user_entity');
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
     * Get user security identifiers for given query.
     *
     * @param string $search Query string
     * @return string[]
     * @deprecated remove in FOM v3.3 (no invocations)
     */
    public function getUsers($search)
    {
        $qb = $this->getUserRepository()->createQueryBuilder('u');

        $query = $qb->where($qb->expr()->like('LOWER(u.username)', ':search'))
            ->setParameter(':search', '%' . strtolower($search) . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery();

        $result = array();
        foreach($query->getResult() as $user) {
            /** @var User $user */
            $result[] = 'u:' . $user->getUsername();
        }
        return $result;
    }

    /**
     * Get role identifiers
     *
     * @return string[]
     * @deprecated remove in FOM v3.3 (no invocations)
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->getAllGroups() as $group) {
            $roles[] = 'r:' . $group->getRole();
        }

        return $roles;
    }

    /**
     * @return Group[]
     */
    public function getAllGroups()
    {
        $repo = $this->getRepository('FOMUserBundle:Group');
        return $repo->findAll();
    }

    /**
     * @return UserSecurityIdentity[]
     */
    public function getLdapUsers()
    {
        return $this->ldapUserIdentitiesProvider->getIdentities('*');
    }

    /**
     * @return UserSecurityIdentity[]
     */
    public function getDataBaseUserIdentities()
    {
        $identities = array();
        foreach ($this->getDatabaseUsers() as $user) {
            $identities[] = UserSecurityIdentity::fromAccount($user);
        }
        return $identities;
    }

    /**
     * @return User[]
     */
    public function getDatabaseUsers()
    {
        return $this->getUserRepository()->findAll();
    }

    /**
     * @return UserSecurityIdentity[]
     */
    public function getAllUsers()
    {
        return array_merge($this->getLdapUsers(), $this->getDataBaseUserIdentities());
    }

    /**
     * @return EntityRepository
     */
    public function getUserRepository()
    {
        return $this->getDoctrine()->getRepository($this->userEntityClass);
    }
}
