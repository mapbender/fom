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
    /** @var string|null */
    protected $filter;

    /**
     * @param Client $client
     * @param string $baseDn
     * @param string $nameAttribute
     * @param string|null $filter extra LDAP filter
     */
    public function __construct(Client $client, $baseDn, $nameAttribute, $filter)
    {
        $this->client = $client;
        $this->baseDn = $baseDn;
        $this->nameAttribute = $nameAttribute;
        $this->filter = ltrim(rtrim($filter ?: '', ')'), '(') ?: null;
    }

    /**
     * @param string $pattern
     * @return \stdClass[]
     */
    public function getUsers($pattern = '*')
    {
        $filter = $this->getFilterString($pattern);
        $users = array();
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

    /**
     * @param string $namePattern
     * @return string
     */
    protected function getFilterString($namePattern)
    {
        $baseFilter = "({$this->nameAttribute}={$namePattern})";
        if ($this->filter) {
            return "(&{$baseFilter}({$this->filter}))";
        } else {
            return $baseFilter;
        }
    }
}
