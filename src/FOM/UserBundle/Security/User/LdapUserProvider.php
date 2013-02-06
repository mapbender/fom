<?php

namespace FOM\UserBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Bridge\Monolog\Logger;
use Mapbender\Component\Ldap;

class LdapUserProvider implements UserProviderInterface
{
    protected $params;
    protected $logger;

    public function __construct(array $fom_params, Logger $logger)
    {
        $this->params = $fom_params['ldap'];
        $this->logger = $logger;
    }

    public function loadUserByUsername($username, $isDn=false)
    {
        // Find remote user
        $ldap = new Ldap($this->params['host'],
            $this->params['port'],
            $this->params['version']);

        $base = $this->params['base_dn'];
        $filter = sprintf($this->params['filter'], $username);
        if($isDn) {
            $filter = sprintf($this->params['filter'], '*');
            $base = $username;
        }

        $result = $ldap->search($base, $filter);

        $this->logger->debug(sprintf('LDAP search with base dn "%s" and filter "%s" yielded: %s',
            $base, $filter, print_r($result, true)));

        if(false === $result) {
            throw new UsernameNotFoundException(
                sprintf('No record found for user %s', $username));
        }

        if(!array_key_exists(0, $result)) {
            print_r($result);die($filter . '  -  ' . $base);
        }

        $user_class = $this->params['user_class'];
        $user = new $user_class();
        $user->setLdapData($result[0]);

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername(), true);
    }

    public function supportsClass($class)
    {
        return $class === $this->params['user_class'];
    }
}
