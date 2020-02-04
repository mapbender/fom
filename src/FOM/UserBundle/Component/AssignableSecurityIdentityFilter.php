<?php


namespace FOM\UserBundle\Component;


use FOM\UserBundle\Entity\Group;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Controls which security identities will be listed when picking security identities to add to an Acl.
 *
 * This is a post-filter because FOMIdentitiesProvider implementations have a long, bad history of
 * customization, with lots of different value types returned, and no angle to inject new configured
 * behaviours.
 */
class AssignableSecurityIdentityFilter
{
    /** @var IdentitiesProviderInterface */
    protected $provider;

    // @todo: inject configuration parameters
    protected $allowUsers = true;
    protected $allowGroups = true;
    protected $allowAuthenticated = false;  // @todo: this is the major new feature, enable it
    protected $allowAnonymous = false;  // @todo: remove special snowflaking from template groups-and-users.html.twig

    protected $warningMessages = array();

    /** @var DummyGroup */
    protected $anonGroup;
    /** @var DummyGroup */
    protected $authenticatedGroup;

    public function __construct(IdentitiesProviderInterface $provider)
    {
        $this->provider = $provider;
        // @todo: translate default group titles (here, once)
        $this->anonGroup = new DummyGroup('IS_AUTHENTICATED_ANONYMOUSLY', 'IS_AUTHENTICATED_ANONYMOUSLY');
        $this->authenticatedGroup = new DummyGroup('ROLE_USER', 'ROLE_USER');
    }

    /**
     * @return Group[]
     */
    public function getAssignableGroups()
    {
        $builtInRoles = array(
            'ROLE_USER',
            'IS_AUTHENTICATED_ANONYMOUSLY',
        );
        $groups = array();
        if ($this->allowGroups) {
            foreach ($this->provider->getAllGroups() as $providerGroup) {
                $groupIdent = $this->normalizeGroup($providerGroup);
                if (!\in_array($groupIdent->getRole(), $builtInRoles)) {
                    $groups[] = $groupIdent;
                }
            }
        }
        if ($this->allowAuthenticated) {
            $groups[] = $this->authenticatedGroup;
        }
        if ($this->allowAnonymous) {
            $groups[] = $this->anonGroup;
        }
        return $groups;
    }

    /**
     * @return UserSecurityIdentity[]
     */
    public function getAssignableUsers()
    {
        $users = array();
        if ($this->allowUsers) {
            foreach ($this->provider->getAllUsers() as $providerUser) {
                $users[] = $this->normalizeUser($providerUser);
            }
        }
        return $users;
    }

    /**
     * @param mixed $value
     * @return Group
     * @throws \InvalidArgumentException
     */
    protected function normalizeGroup($value)
    {
        if (is_object($value)) {
            if ($value instanceof Group) {
                return $value;
            }
            if ($value instanceof RoleInterface) {
                // no warning. Convert to unpersisted group for template compatibility (getAsRole)
                $group = new Group();
                $group->setTitle(preg_replace('#^ROLE_(GROUP_)?#', '', $value->getRole()));
                return $group;
            }
            $cls = get_class($value);
            $this->warnOnce("Group identities should be Group or RoleInterface, not {$cls} objects", "group:{$cls}");
            if ($value instanceof RoleSecurityIdentity) {
                // exact same treatment as RoleInterface, but AFTER emitting the appropriate warning
                return $this->normalizeGroup(new Role($value->getRole()));
            } elseif ($value instanceof \stdClass) {
                $values = $this->extractStdClass($value);
                foreach (array('title', 'getTitle') as $titleCandidate) {
                    if (!empty($values[$titleCandidate])) {
                        $group = new Group();
                        $group->setTitle($values[$titleCandidate]);
                        return $group;
                    }
                }
                foreach (array('role', 'getRole', 'getAsRole') as $roleCandidate) {
                    if (!empty($values[$roleCandidate])) {
                        return $this->normalizeGroup(new Role($values[$roleCandidate]));
                    }
                }
                throw new \InvalidArgumentException("Don't know how to transform stdClass group input with keys " . implode(',', array_keys($values)) . " to Group");
            } elseif (\method_exists($value, '__toString') && !($value instanceof SecurityIdentityInterface)) {
                return $this->normalizeGroup(strval($value));
            } else {
                throw new \InvalidArgumentException("Don't know how to transform {$cls} group input to Group");
            }
        } elseif (\is_string($value)) {
            $this->warnOnce("Group identities should be RoleSecurityIdentity objects, not strings", "group:string");
            // strip leading 'r:'
            return $this->normalizeGroup(new Role(preg_replace('#^r:#', '', $value)));
        } else {
            throw new \InvalidArgumentException("Don't know how to transform " . gettype($value) . " group input to Group");
        }
    }

    /**
     * @param mixed $value
     * @return UserSecurityIdentity
     * @throws \InvalidArgumentException
     */
    protected function normalizeUser($value)
    {
        if (is_object($value)) {
            if ($value instanceof UserSecurityIdentity) {
                return $value;
            }
            $cls = get_class($value);
            $this->warnOnce("User identities should be UserSecurityIdentity, not {$cls} objects", "user:{$cls}");
            if ($value instanceof UserInterface) {
                return UserSecurityIdentity::fromAccount($value);
            } elseif ($value instanceof \stdClass) {
                $values = $this->extractStdClass($value);
                $username = null;
                $userclass = null;
                foreach (array('username', 'getUsername') as $nameCandidate) {
                    if (!empty($values[$nameCandidate])) {
                        $username = $values[$nameCandidate];
                        break;
                    }
                }
                foreach (array('class', 'getClass') as $classCandidate) {
                    if (!empty($values[$classCandidate])) {
                        $userclass = $values[$classCandidate];
                        break;
                    }
                }
                if ($userclass && $username) {
                    return new UserSecurityIdentity($username, $userclass);
                }
                throw new \InvalidArgumentException("Don't know how to transform stdClass user input with keys " . implode(',', array_keys($values)) . " to UserSecurityIdentity");
            } else {
                throw new \InvalidArgumentException("Don't know how to transform {$cls} user input to UserSecurityIdentity");
            }
        } else {
            throw new \InvalidArgumentException("Don't know how to transform " . gettype($value) . " group input to UserSecurityIdentity");
        }
    }

    protected function warnOnce($message, $key = null)
    {
        $key = $key ?: $message;
        if (empty($this->warningMessages[$key])) {
            // @todo: add throwing strict mode
            // NOTE: E_USER_DEPRECATED is the only error class that will reliably go to the log without throwing an
            //       exception.
            @trigger_error("WARNING: {$message}", E_USER_DEPRECATED);
            $this->warningMessages[$key] = true;
        }
    }

    protected static function extractStdClass($o)
    {
        $values = array();
        foreach ((array)$o as $name => $value) {
            if (\is_callable($value)) {
                $values[$name] = $value();
            } else {
                $values[$name] = $value;
            }
        }
        return $values;
    }
}
