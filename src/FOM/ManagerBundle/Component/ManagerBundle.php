<?php

namespace FOM\ManagerBundle\Component;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ManagerBundle base class.
 *
 * This class is the base class for bundles implementing Manager functionality.
 *
 * @author Christian Wygoda
 */
class ManagerBundle extends Bundle
{
    /**
     * Getter for list of controllers to embed into Manager interface.
     *
     * The list must be an array of arrays, each giving the integer weight, name, route and array of route prefixes
     * to match against. See source for an example.
     *
     * @return array
     */
    public function getManagerControllers()
    {
        /* Example below
        return array(
            array(
                weight => 5,
                name => 'Users',
                route => 'fom_user_useranager_index',
                routes => array(
                    'fom_user_usermanager',
                    'fom_user_rolemanager'
                )
            )
        );
         */
    }
}

