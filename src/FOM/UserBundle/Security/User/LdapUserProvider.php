<?php

namespace FOM\UserBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Mapbender\Component\Ldap;

/**
 * Description of LdapUserProvider
 *
 * @author arp
 */
class LdapUserProvider implements UserProviderInterface
{
    protected $container;
    protected $ldapConfiguration;


    public function __construct($ldapConfiguration)
    {
        $this->ldapConfiguration = $ldapConfiguration['ldap'];
    }
    
    public function loadUserByUsername($username)
    {
        $ldap = new Ldap($this->ldapConfiguration['host'],
            $this->ldapConfiguration['port'],
            $this->ldapConfiguration['version']);
        
        if(!$ldap) {
            throw new \RuntimeException('LDAP configuration error: ' . $ldap->lastError());
        }

        $data = $ldap->search($this->ldapConfiguration['base_dn'], 
             sprintf($this->ldapConfiguration['filter'], $username));

        if(!isset($data[0]['dn'])) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
            
        $user = new $this->ldapConfiguration['user_class']();
        /*
        foreach($this->ldapConfiguration['user_mapping'] as $target => $source) {
            $method = 'get' . ucfirst($target);
            $value = $data[0][$source];
            $user->$method($value);
        }
         */
        
        if(method_exists($user, 'setLdapData')) {
            $user->setLdapData($data[0]);
        }
        
        return $user;
    }
    
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof XXXX) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === $this->userClass;
    }
}
