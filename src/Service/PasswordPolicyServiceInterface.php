<?php

namespace Despark\PasswordPolicyBundle\Service;

interface PasswordPolicyServiceInterface
{
    /**
     * @param string $password
     * @param \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @return bool
     */
    public function getHistoryByPassword(
        string $password,
        HasPasswordPolicyInterface $entity
    ): ?PasswordHistoryInterface;
}