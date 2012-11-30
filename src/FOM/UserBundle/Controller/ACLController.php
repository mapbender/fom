<?php

namespace FOM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class ACLController extends Controller
{
    /**
     * ACL Security Identity typeahead callback
     * If query starts with 'u:', look for user, if it starts with 'r:', look
     * for role, otherwise look for both.
     * 
     * @Route("/acl/sid")
     */
    public function aclsidAction()
    {
        $query = $this->get('request')->get('query');
        $response = array();

        if($query !== null) {
            switch(substr($query, 0, 2)) {
                case 'u:':
                    $response = $this->getUsers(substr($query, 3));
                    break;
                case 'r:':
                    $response = $this->getRoles(substr($query, 3));
                    break;
                default:
                    $response = array_merge(
                        $this->getUsers(substr($query, 3)),
                        $this->getRoles(substr($query, 3)));
            }
        }

        return new Response(json_encode($response), 200, array(
            'Content-Type' => 'application/json'));
    }

    /**
     * Get user security identifiers for given query.
     * 
     * @param  string $query Query string
     * @return array         Array of user security identifiers
     */
    protected function getUsers($query)
    {
        return array(
            'u:abc',
            'u:cdf'
        );
    }

    /**
     * Get role security identifiers for given query.
     * 
     * @param  string $query Query string
     * @return array         Array of role security identifiers
     */
    protected function getRoles($query) {
        return array(
            'r:zyx',
            'r:123'
        );
    }
}
