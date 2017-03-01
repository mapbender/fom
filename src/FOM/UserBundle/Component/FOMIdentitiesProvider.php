<?php

namespace FOM\UserBundle\Component;

use Symfony\Component\DependencyInjection\ContainerAware;

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
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    /**
     * @param string $search
     * @return array
     */
    public function getUsers($search)
    {
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        $qb = $repo->createQueryBuilder('u');

        $query = $qb->where($qb->expr()->like('LOWER(u.username)', ':search'))
            ->setParameter(':search', '%' . strtolower($search) . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery();

        $result = array();
        foreach($query->getResult() as $user) {
            $result[] = 'u:' . $user->getUsername();
        }
        return $result;
    }

    public function getRoles() {
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
        $groups = $repo->findAll();

        $roles = array();
        foreach($groups as $group) {
            $roles[] = 'r:' . $group->getAsRole();
        }

        return $roles;
    }

    public function getAllGroups(){
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:Group');
        $groups = $repo->findAll();

        $all = array();
        foreach($groups as $group) {
            $all[] = $group;
        }

        return $all;
    }

    public function getAllUsers(){
        // Settings for LDAP
        if($this->container->hasParameter('ldap_host')) {
            $ldapHostname = $this->container->getParameter("ldap_host");
            $ldapPort = $this->container->getParameter("ldap_port");
            $ldapVersion = $this->container->getParameter("ldap_version");
            $baseDn = $this->container->getParameter("ldap_user_base_dn");
            $nameAttribute = $this->container->getParameter("ldap_user_name_attribute");
            $bindDn = $this->container->getParameter("ldap_bind_dn");
            $bindPasswd = $this->container->getParameter("ldap_bind_pwd");
            $filter = "(" . $nameAttribute . "=*)";

            $connection = @ldap_connect($ldapHostname, $ldapPort);
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, $ldapVersion);

            if (strlen($bindDn) !== 0 && strlen($bindPasswd) !== 0) {
                if (!ldap_bind($connection, $bindDn, $bindPasswd)) {
                    throw new \Exception('Unable to bind LDAP to DN: ' . $bindDn);
                }
            }

            $ldapListRequest = ldap_list($connection, $baseDn, $filter); // or throw exeption('Unable to list. LdapError: ' . ldap_error($ldapConnection));

            $ldapUserList = ldap_get_entries($connection, $ldapListRequest);
        }

        // Settings for local user database
        $repo = $this->getDoctrine()->getRepository('FOMUserBundle:User');
        $users = $repo->findAll();

        $all = array();

        if($this->container->hasParameter('ldap_host')) {
            // Add Users from LDAP
            foreach($ldapUserList as $ldapUser) {
                $user = new \stdClass;
                $user->getUsername = $ldapUser[$nameAttribute][0];
                $all[] = $user;
            }
        }
        // Add Mapbenderusers from database
        foreach($users as $user) {
            $all[] = $user;
        }
        return $all;
    }
}
