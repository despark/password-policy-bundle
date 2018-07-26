<?php


namespace Despark\PasswordPolicyBundle\Service;


use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PasswordExpiryService implements PasswordExpiryServiceInterface
{
    /**
     * @var \Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration[]
     */
    private $entities;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private $router;

    /**
     * PasswordExpiryService constructor.
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     */
    public function __construct(TokenStorageInterface $tokenStorage, UrlGeneratorInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    /**
     * @return bool
     */
    public function isPasswordExpired(): bool
    {
        /** @var HasPasswordPolicyInterface $user */
        if ($user = $this->getCurrentUser()) {
            foreach ($this->entities as $class => $config) {
                $passwordLastChange = $user->getPasswordChangedAt();
                if ($passwordLastChange && $user instanceof $class) {
                    $expiresAt = (clone $passwordLastChange)->modify('+'.$config->getExpiryDays().' days');

                    return $expiresAt <= new \DateTime();
                }
            }
        }


        return false;
    }

    /**
     * @param string|null $entityClass
     * @param array $params
     * @return string
     */
    public function generateLockedRoute(string $entityClass = null, array $params = []): string
    {
        $lockedRoute = $this->getLockedRoute($entityClass);
        $lockedParams = $this->getLockedRouteParams($entityClass);

        $params = array_merge($lockedParams, $params);

        foreach ($params as $param => &$value) {
            if ($value === '{id}') {
                $value = $this->getCurrentUser() ? $this->getCurrentUser()->getId() : $value;
            }
        }
        if ($lockedRoute) {
            return $this->router->generate($lockedRoute, $params);
        }
        return '';
    }

    /**
     * @param string|null $entityClass
     * @return array
     */
    public function getLockedRouteParams(string $entityClass = null): array
    {
        $entityClass = $this->prepareEntityClass($entityClass);

        return isset($this->entities[$entityClass]) ? $this->entities[$entityClass]->getLockedRouteParams() : [];
    }

    /**
     * @param string $entityClass
     * @return string
     */
    public function getLockedRoute(string $entityClass = null): ?string
    {
        $entityClass = $this->prepareEntityClass($entityClass);

        return isset($this->entities[$entityClass]) ? $this->entities[$entityClass]->getLockRoute() : null;
    }

    /**
     * @param string $entityClass
     * @return array
     */
    public function getExcludedRoutes(string $entityClass = null): array
    {
        $entityClass = $this->prepareEntityClass($entityClass);

        return isset($this->entities[$entityClass]) ? $this->entities[$entityClass]->getExcludedRoutes() : [];
    }

    /**
     * @param \Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration $configuration
     */
    public function addEntity(PasswordExpiryConfiguration $configuration): void
    {
        $this->entities[$configuration->getEntityClass()] = $configuration;
    }

    /**
     * @return \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|null
     */
    private function getCurrentUser(): ?HasPasswordPolicyInterface
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $user = $token->getUser()) {
            if ($user === 'anon.') {
                return null;
            }

            return $user instanceof HasPasswordPolicyInterface ? $user : null;
        }

        return null;
    }

    /**
     * @param string $entityClass
     * @return string
     */
    private function prepareEntityClass(?string $entityClass): ?string
    {
        if (is_null($entityClass) && $user = $this->getCurrentUser()) {
            $entityClass = get_class($user);
        }

        return $entityClass;
    }

}