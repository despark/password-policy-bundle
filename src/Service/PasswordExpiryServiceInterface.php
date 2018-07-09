<?php

namespace Despark\PasswordPolicyBundle\Service;

use Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;

interface PasswordExpiryServiceInterface
{
    /**
     * @return bool
     */
    public function isPasswordExpired(): bool;

    /**
     * @param string|null $entityClass
     * @param array $params
     * @return string
     */
    public function generateLockedRoute(string $entityClass = null, array $params = []): string;

    /**
     * @param string|null $entityClass
     * @return array
     */
    public function getLockedRouteParams(string $entityClass = null): array;

    /**
     * @param string $entityClass
     * @return null|string
     */
    public function getLockedRoute(string $entityClass = null): ?string;

    /**
     * @param string $entityClass
     * @return array
     */
    public function getExcludedRoutes(string $entityClass = null): array;

    /**
     * @param \Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration $configuration
     * @return void
     */
    public function addEntity(PasswordExpiryConfiguration $configuration): void;

}