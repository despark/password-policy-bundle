<?php

namespace Despark\Bundle\PasswordPolicyBundle\Service;

use Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;

interface PasswordHistoryServiceInterface
{
    /**
     * @param \Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param int $historyLimit
     */
    public function cleanupHistory(HasPasswordPolicyInterface $entity, int $historyLimit): void;
}