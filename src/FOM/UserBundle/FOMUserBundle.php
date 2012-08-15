<?php

namespace FOM\UserBundle;

use FOM\ManagerBundle\Component\ManagerBundle;

/**
 * FOMUserBundle - provides user management
 *
 * @author Christian Wygoda
 */
class FOMUserBundle extends ManagerBundle
{
    /**
     * @inheritdoc
     */
    public function getManagerControllers()
    {
        return array(
            array(
                'title' => 'Users',
                'weight' => 100,
                'route' => 'fom_user_user_index',
                'routes' => array(
                    'fom_user_user',
                    'fom_user_role'
                )
            )
        );
    }
}

