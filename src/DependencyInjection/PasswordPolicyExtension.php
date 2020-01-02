<?php


namespace Despark\PasswordPolicyBundle\DependencyInjection;


use Despark\PasswordPolicyBundle\EventListener\PasswordEntityListener;
use Despark\PasswordPolicyBundle\EventListener\PasswordExpiryListener;
use Despark\PasswordPolicyBundle\Exceptions\ConfigurationException;
use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;
use Despark\PasswordPolicyBundle\Service\PasswordExpiryService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PasswordPolicyExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->addExpiryListener($container, $config);

        $expiryService = $container->getDefinition(PasswordExpiryService::class);

        foreach ($config['entities'] as $entityClass => $settings) {
            if (!class_exists($entityClass)) {
                throw new ConfigurationException(sprintf('Entity class %s not found', $entityClass));
            }

            $this->addEntityListener($container, $entityClass, $settings);

            $passwordExpiryConfig = $container->register(
                'password_expiry_configuration.'.$entityClass,
                PasswordExpiryConfiguration::class
            );
            $passwordExpiryConfig->setArguments([
                $entityClass,
                $settings['expiry_days'],
                $settings['lock_route'],
                $settings['lock_route_params'] ?? [],
                $settings['excluded_routes'],
            ]);

            $expiryService->addMethodCall('addEntity', [$passwordExpiryConfig]);
        }

    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     * @return \Symfony\Component\DependencyInjection\Definition
     */
    private function addExpiryListener(ContainerBuilder $container, array $config): Definition
    {
        return $container->autowire(PasswordExpiryListener::class)
                         ->addTag('kernel.event_listener', [
                             'event' => 'kernel.request',
                             'priority' => $config['expiry_listener']['priority'],
                         ])
                         ->setArgument('$errorMessage', $config['expiry_listener']['error_msg']['text'])
                         ->setArgument('$errorMessageType', $config['expiry_listener']['error_msg']['type']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $entityClass
     * @param $settings
     * @return \Symfony\Component\DependencyInjection\Definition
     * @throws \Despark\PasswordPolicyBundle\Exceptions\ConfigurationException
     */
    private function addEntityListener(
        ContainerBuilder $container,
        string $entityClass,
        array $settings
    ): Definition {
        if (!is_a($entityClass, HasPasswordPolicyInterface::class, true)) {
            throw new ConfigurationException(sprintf('Entity %s doesn\'t implement %s interface', $entityClass,
                HasPasswordPolicyInterface::class));
        }

        $snakeClass = strtolower(str_replace('\\', '_', $entityClass));
        $entityListener = $container->autowire('password_policy.entity_listener.'.$snakeClass,
            PasswordEntityListener::class);

        $entityListener->addTag('doctrine.event_listener', ['event' => 'onFlush']);

        $entityListener->setArgument('$passwordField', $settings['password_field']);
        $entityListener->setArgument('$passwordHistoryField', $settings['password_history_field']);
        $entityListener->setArgument('$historyLimit', $settings['passwords_to_remember']);
        $entityListener->setArgument('$entityClass', $entityClass);

        return $entityListener;
    }


}