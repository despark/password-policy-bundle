<?php

namespace Despark\PasswordPolicyBundle\Service;

interface PasswordExpiryServiceInterface
{
    public function isPasswordExpired(): bool;

    /**
     * @param string $class
     * @param $expiryDays
     * @throws \Despark\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    public function addEntity(string $class, int $expiryDays);
}