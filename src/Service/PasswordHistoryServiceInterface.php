<?php

namespace Despark\PasswordPolicyBundle\Service;

use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;

interface PasswordHistoryServiceInterface
{
    /**
     * @param \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param int $historyLimit
     * @return array
     */
    public function getHistoryItemsForCleanup(HasPasswordPolicyInterface $entity, int $historyLimit): array;
}