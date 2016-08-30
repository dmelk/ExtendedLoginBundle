<?php

namespace Melk\ExtendedLoginBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('melk_extended_login');

        $rootNode
            ->children()
                ->scalarNode('client')
                    ->defaultValue('captcha_login')
                ->end()
                ->integerNode('max_attempts')
                    ->defaultValue(5)
                ->end()
                ->integerNode('attempts_period')
                    ->defaultValue(300)
                ->end()
                ->integerNode('captcha_period')
                    ->defaultValue(1800)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
