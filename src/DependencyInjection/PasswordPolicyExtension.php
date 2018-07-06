<?php


namespace Despark\Bundle\PasswordPolicyBundle\DependencyInjection;


use Despark\Bundle\PasswordPolicyBundle\EventListener\PasswordEntityListener;
use Despark\Bundle\PasswordPolicyBundle\EventListener\PasswordExpiryListener;
use Despark\Bundle\PasswordPolicyBundle\Exceptions\ConfigurationException;
use Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\Bundle\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        foreach ($config['entities'] as $entityClass => $settings) {
            if (!class_exists($entityClass)) {
                throw new ConfigurationException(sprintf('Entity class %s not found', $entityClass));
            }

            $this->addEntityListener($container, $entityClass, $settings);
        }

        $this->addExpiryListener($container, $config);
    }

    private function addExpiryListener(ContainerBuilder $container, array $config)
    {
        $expiryConfig = $config['expiry'];

        $container->register(PasswordExpiryListener::class)
                  ->addTag('kernel.event_listener', [
                      'event' => 'kernel.request',
                      'priority' => $expiryConfig['listener_priority'],
                  ])
                  ->setArguments([
                      $expiryConfig['expiry_days'],
                  ]);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $entityClass
     * @param $settings
     * @throws \Despark\Bundle\PasswordPolicyBundle\Exceptions\ConfigurationException
     */
    private function addEntityListener(
        ContainerBuilder $container,
        string $entityClass,
        array $settings
    ): void {
        if (!is_a($entityClass, HasPasswordPolicyInterface::class, true)) {
            throw new ConfigurationException(sprintf('Entity %s doesn\'t implement %s interface', $entityClass,
                HasPasswordPolicyInterface::class));
        }

        $snakeClass = strtolower(str_replace('\\', '_', $entityClass));
        $entityListener = $container->register('password_policy.entity_listener.'.$snakeClass)
                                    ->setClass(PasswordEntityListener::class);

        $entityListener->addTag('doctrine.event_listener', ['event' => 'onFlush']);
        $entityListener->setArguments([
            $settings['password_field'],
            $settings['password_history_field'],
            $settings['passwords_to_remember'],
            $container->getDefinition(PasswordHistoryServiceInterface::class),
        ]);
    }


}