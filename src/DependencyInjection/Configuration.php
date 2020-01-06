<?php


namespace Despark\PasswordPolicyBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    private const DEFAULT_PASSWORD_FIELD = 'password';
    private const DEFAULT_PASSWORD_HISTORY_FIELD = 'passwordHistory';
    private const DEFAULT_PASSWORDS_TO_REMEMBER = 3;
    private const DEFAULT_EXPIRY_LISTENER_PRIORITY = 0;
    private const DEFAULT_EXPIRY_DAYS = 90;
    private const DEFAULT_ERROR_MSG = 'Your password expired. You need to change it';
    private const DEFAULT_ERROR_TYPE = 'error';

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
                        ->isRequired()
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
                                ->integerNode('expiry_days')
                                    ->defaultValue(self::DEFAULT_EXPIRY_DAYS)
                                    ->treatNullLike(self::DEFAULT_EXPIRY_DAYS)
                                ->end()
                                ->scalarNode('lock_route')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->arrayNode('lock_route_params')
                                    ->useAttributeAsKey('param')
                                    ->scalarPrototype()->end()
                                ->end()
                                ->arrayNode('excluded_routes')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('expiry_listener')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('priority')
                                ->defaultValue(self::DEFAULT_EXPIRY_LISTENER_PRIORITY)
                                ->treatNullLike(self::DEFAULT_EXPIRY_LISTENER_PRIORITY)
                            ->end()
                            ->arrayNode('error_msg')
                            ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('text')
                                        ->defaultValue(self::DEFAULT_ERROR_MSG)
                                        ->treatNullLike(self::DEFAULT_ERROR_MSG)
                                    ->end()
                                    ->scalarNode('type')
                                        ->defaultValue(self::DEFAULT_ERROR_TYPE)
                                        ->treatNullLike(self::DEFAULT_ERROR_TYPE)
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                 ->end();
        //@formatter:on

        return $treeBuilder;
    }
}