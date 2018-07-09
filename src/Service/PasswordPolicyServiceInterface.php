<?php

namespace Despark\PasswordPolicyBundle\Service;

use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface PasswordPolicyServiceInterface
{
    /**
     * @param string $password
     * @param \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @return \Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface|null
     */
    public function getHistoryByPassword(
        string $password,
        HasPasswordPolicyInterface $entity
    ): ?PasswordHistoryInterface;
}