<?php


namespace FOM\UserBundle\Component\Ldap;

/**
 * Service registered as fom.ldap_user_provider
 * @since v3.1.7
 * @since v3.2.7
 */
class UserProvider
{
    /** @var Client */
    protected $client;
    /** @var string */
    protected $baseDn;
    /** @var string */
    protected $nameAttribute;
    /** @var string */
    protected $filterTemplate;

    /**
     * @param Client $client
     * @param string $baseDn
     * @param string $nameAttribute
     * @param string $filterTemplate pattern inserted via sprintf (should contain single '%s' placeholder)
     */
    public function __construct(Client $client, $baseDn, $nameAttribute, $filterTemplate)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->nameAttribute = $nameAttribute;
        $this->filterTemplate = $filterTemplate;
    }

    /**
     * @param string $pattern
     * @return \stdClass[]
     */
    public function getUsers($pattern = '*')
    {
        $users = array();
        $filter = sprintf($this->filterTemplate, $pattern);
        foreach ($this->client->getObjects($this->baseDn, $filter) as $userRecord) {
            $u = new \stdClass();
            $u->getUsername = $userRecord[$this->nameAttribute][0];
            $users[] = $u;
        }
        return $users;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function userExists($name)
    {
        // NOTE: ldap_escape implementation is provided by symfony/polyfill-php56 even on older PHP versions
        $pattern = \ldap_escape($name, null, LDAP_ESCAPE_FILTER);
        return !empty($this->getUsers($pattern));
    }
}
