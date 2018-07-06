<?php


namespace Despark\Bundle\PasswordPolicyBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    private const DEFAULT_PASSWORD_FIELD = 'password';
    private const DEFAULT_PASSWORD_HISTORY_FIELD = 'passwordHistory';
    private const DEFAULT_PASSWORDS_TO_REMEMBER = 3;
    private const DEFAULT_EXPIRY_LISTENER_PRIORITY = 2000;
    private const DEFAULT_EXPIRY_DAYS = 90;

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('password_policy');

        // @formatter:off
        $rootNode->fixXmlConfig('entity')
                 ->children()
                    ->arrayNode('entities')
                        ->useAttributeAsKey('class')
                        ->cannotBeEmpty()
                        ->arrayPrototype()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('password_field')
                                    ->defaultValue(self::DEFAULT_PASSWORD_FIELD)
                                    ->treatNullLike(self::DEFAULT_PASSWORD_FIELD)
                                ->end()
                                ->scalarNode('password_history_field')
                                    ->defaultValue(self::DEFAULT_PASSWORD_HISTORY_FIELD)
                                    ->treatNullLike(self::DEFAULT_PASSWORD_HISTORY_FIELD)
                                ->end()
                                ->integerNode('passwords_to_remember')
                                    ->defaultValue(self::DEFAULT_PASSWORDS_TO_REMEMBER)
                                    ->treatNullLike(self::DEFAULT_PASSWORDS_TO_REMEMBER)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('expiry')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('listener_priority')
                                ->defaultValue(self::DEFAULT_EXPIRY_LISTENER_PRIORITY)
                                ->treatNullLike(self::DEFAULT_EXPIRY_LISTENER_PRIORITY)
                            ->end()
                            ->integerNode('expiry_days')
                                ->defaultValue(self::DEFAULT_EXPIRY_DAYS)
                                ->treatNullLike(self::DEFAULT_EXPIRY_DAYS)
                            ->end()
                        ->end()
                    ->end()

                 ->end();
        //@formatter:on

        return $treeBuilder;
    }
}