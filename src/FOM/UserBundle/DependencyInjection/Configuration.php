<?php

/**
 * @author Christian Wygoda
 */

namespace FOM\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fom_user');

        $rootNode
            ->children()
                ->scalarNode('selfregister')
                    ->defaultFalse()
                ->end()
                ->scalarNode('reset_password')
                    ->defaultTrue()
                ->end()
                ->scalarNode('max_registration_time')
                    ->defaultValue(24)
                ->end()
                ->scalarNode('max_reset_time')
                    ->defaultValue(24)
                ->end()
                ->scalarNode('mail_from_address')
                    ->isRequired()
                ->end()
                ->scalarNode('mail_from_name')
                    ->isRequired()
                ->end()
                ->scalarNode('profile_entity')
                    ->defaultNull()
                ->end()
                ->scalarNode('profile_formtype')
                    ->defaultNull()
                ->end()
                ->scalarNode('profile_template')
                    ->defaultNull()
                ->end()
                ->arrayNode('self_registration_groups')
                    ->prototype('scalar')->end()
                    ->defaultValue(array())
                ->end()
                ->scalarNode('use_sspi')
                    ->defaultFalse()
                ->end()
                ->scalarNode('trust_sspi')
                    ->defaultFalse()
                ->end()
                ->scalarNode('identities_provider')
                    ->defaultValue('FOM\UserBundle\Component\FOMIdentitiesProvider')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
