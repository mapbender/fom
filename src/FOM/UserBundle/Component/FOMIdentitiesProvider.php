<?php

namespace FOM\UserBundle\Component;

use FOM\UserBundle\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Ldap\LdapClientInterface;

/**
 * Managed users
 *
 * @alias UserManager
 * @alias User Manager
 *d
 */
class FOMIdentitiesProvider extends ContainerAware implements IdentitiesProviderInterface
{

    /**
     * @param string $search
     * @return array
     */
    public function getUsers($search)
    {
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        $qb   = $repo->createQueryBuilder('u');

        $query = $qb->where($qb->expr()->like('LOWER(u.username)', ':search'))
            ->setParameter(':search', '%' . strtolower($search) . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery();

        $result = array();
        foreach ($query->getResult() as $user) {
            $result[] = 'u:' . $user->getUsername();
        }
        return $result;
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    public function getRoles()
    {
        $repo   = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
        $groups = $repo->findAll();

        $roles = array();
        foreach ($groups as $group) {
            $roles[] = 'r:' . $group->getAsRole();
        }

        return $roles;
    }

    public function getAllGroups()
    {
        $all = array();
        if ($this->container->hasParameter('ldap_host')) {
            $groupDn        = $this->container->getParameter('ldap_group_dn');
            $ldapClient   = $this->getLdapClient();
            $ldapGroupList = $ldapClient->find($groupDn, '(objectClass=top)');
            if ($ldapGroupList != null) {
                foreach (array_slice($ldapGroupList, 2) as $ldapGroup) {
                    $group =    new Group();
                    $group->setTitle( $ldapGroup['cn'][0]);
                    $all[] = $group;
                }

            }

        }


        $repo   = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
        $groups = $repo->findAll();

        foreach ($groups as $group) {

            $all[] = $group;
        }
        return $all;
    }

    public function getAllUsers()
    {
        // Settings for LDAP
        $all = array();
        if ($this->container->hasParameter('ldap_host')) {
            $nameAttribute = $this->container->getParameter('ldap_group_name_attribute');
            $userDn        = $this->container->getParameter('ldap_user_base_dn');

            $ldapClient   = $this->getLdapClient();
            $ldapUserList = $ldapClient->find($userDn, '(objectclass=top)');
            if ($ldapUserList !=  null) {
                unset($ldapUserList[0]);
                foreach (array_slice($ldapUserList, 1) as $ldapUser) {

                    $userName = $ldapUser[ $nameAttribute ][0];
                    $user     = new \stdClass();
                    $user->getUsername = $ldapUser[ $nameAttribute ][0];
                    $all[] = $user;
                }

            }

        }

        // Settings for local user database
        $repo  = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        $users = $repo->findAll();

        // Add Mapbenderusers from database
        foreach ($users as $user) {
            $all[] = $user;
        }
        return $all;
    }

    /**
     * @return LdapClientInterface
     */
    private function getLdapClient()
    {


        $ldapClient = $this->container->get('ldapClient');
        $bindDn     = $this->container->getParameter("ldap_bind_dn");
        $bindPasswd = $this->container->getParameter("ldap_bind_pwd");
        $ldapClient->bind($bindDn, $bindPasswd);

        return $ldapClient;
    }
}
