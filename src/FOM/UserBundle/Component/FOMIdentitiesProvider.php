<?php

namespace FOM\UserBundle\Component;

use Doctrine\ORM\EntityRepository;
use FOM\UserBundle\Component\Ldap;
use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Managed users
 *
 * @alias UserManager
 * @alias User Manager
 *d
 */
class FOMIdentitiesProvider implements IdentitiesProviderInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var Ldap\Client */
    protected $client;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = $container->get('fom.ldap_client');
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
     * @param string $search
     * @return array
     */
    public function getUsers($search)
    {
        $repo = $this->getRepository('FOMUserBundle:User');
        $qb = $repo->createQueryBuilder('u');

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
     * @return string[]
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->getAllGroups() as $group) {
            $roles[] = 'r:' . $group->getAsRole();
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

    public function getLdapUsers()
    {
        $users = array();
        $baseDn = $this->container->getParameter('ldap_user_base_dn');
        $nameAttribute = $this->container->getParameter('ldap_user_name_attribute');
        $filter = "(" . $nameAttribute . "=*)";
        foreach ($this->client->getObjects($baseDn, $filter) as $userRecord) {
            $u = new \stdClass();
            $u->getUsername = $userRecord[$nameAttribute][0];
            $users[] = $u;
        }
        return $users;
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
