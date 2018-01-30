<?php

namespace FOM\UserBundle\Component;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use FOM\UserBundle\Entity\Group;
use FOM\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Ldap\LdapClientInterface;

/**
 * Managed users
 *
 * @alias UserManager
 * @alias User Manager
 *d
 */
class FOMIdentitiesProvider implements IdentitiesProviderInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string $search
     * @return array
     */
    public function getUsers($search)
    {
        $repo = $this->getUserRepository();
        $qb   = $repo->createQueryBuilder('u');

        $query = $qb->where($qb->expr()->like('LOWER(u.username)', ':search'))
            ->setParameter(':search', '%' . strtolower($search) . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery();

        /* @var array $users */
        $users  = $query->getResult();
        $result = array();

        /* @var User $user */
        foreach ($users as $user) {
            $result[] = 'u:' . $user->getUsername();
        }
        return $result;
    }

    /**
     * @return EntityRepository
     */
    protected function getUserRepository()
    {
        return $this->getDoctrine()->getRepository('FOMUserBundle:User');
    }

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    /**
     * Returns all groups as string array in the form r:groupName
     *
     * @return array
     */
    public function getRoles()
    {
        /* @var array $groups */
        $groups = $this->getGroupRepository()->findAll();
        $roles  = array();

        /* @var Group $group */
        foreach ($groups as $group) {
            $roles[] = 'r:' . $group->getAsRole();
        }

        return $roles;
    }

    /**
     * @return EntityRepository
     */
    protected function getGroupRepository()
    {
        return $this->getDoctrine()->getRepository('FOMUserBundle:Group');
    }

    /**
     * Returns all groups from the database and LDAP directory as array of group objects
     *
     * @return array
     */
    public function getAllGroups()
    {
        $all = array();
        if ($this->container->hasParameter('ldap.host')) {
            $groupDn       = $this->container->getParameter('ldap.group.baseDn');
            $groupFilter   = $this->container->getParameter('ldap.group.adminFilter');
            $ldapClient    = $this->getLdapClient();
            $ldapGroupList = $ldapClient->find($groupDn, $groupFilter);
            if ($ldapGroupList != null) {
                foreach (array_slice($ldapGroupList, 2) as $ldapGroup) {
                    $group = new Group();
                    $group->setTitle($ldapGroup['cn'][0]);
                    $all[] = $group;
                }

            }

        }

        /* @var array $groups */
        $groups = $this->getGroupRepository()->findAll();

        foreach ($groups as $group) {

            $all[] = $group;
        }

        return $all;
    }

    /**
     * @return LdapClientInterface
     */
    private function getLdapClient()
    {

        /** @var LdapClientInterface $ldapClient */
        $ldapClient = $this->container->get('ldapClient');
        $bindDn     = $this->container->getParameter("ldap.bind.dn");

        $bindPwd = $this->container->getParameter("ldap.bind.pwd");
        $ldapClient->bind($bindDn, $bindPwd);

        return $ldapClient;
    }

    /**
     * Returns all users from the database and LDAP directory as array of user objects
     * @return array
     */
    public function getAllUsers()
    {
        $all = array();
        if ($this->container->hasParameter('ldap.host')) {
            $nameAttribute = $this->container->getParameter('ldap.user.nameAttribute');
            $userDn        = $this->container->getParameter('ldap.user.baseDn');
            $userQuery     = $this->container->getParameter('ldap.user.adminFilter');

            $ldapClient   = $this->getLdapClient();
            $ldapUserList = $ldapClient->find($userDn, $userQuery);

            if ($ldapUserList != null) {

                foreach (array_slice($ldapUserList, 2) as $ldapUser) {
                    $user = new User();
                    $user->setUsername($ldapUser[ $nameAttribute ][0]);
                    $all[] = $user;
                }

            }

        }

        $dataBaseUser = $this->getUserRepository()->findAll();

        // Merge Database and LDAP User
        foreach ($dataBaseUser as $user) {
            $all[] = $user;
        }
        return $all;
    }
}
