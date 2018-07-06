<?php


namespace Despark\Bundle\PasswordPolicyBundle\Model;


use Doctrine\Common\Collections\Collection;

/**
 * Interface HasPasswordPolicyInterface
 * @package Despark\Bundle\PasswordPolicyBundle\Model
 */
interface HasPasswordPolicyInterface
{
    /**
     * @return \DateTime
     */
    public function getPasswordChangedAt(): ?\DateTime;

    /**
     * @param \DateTime $dateTime
     */
    public function setPasswordChangedAt(\DateTime $dateTime): void;

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPasswordHistory(): Collection;

    /**
     * @param \Despark\Bundle\PasswordPolicyBundle\Model\PasswordHistoryInterface $passwordHistory
     */
    public function addPasswordHistory(PasswordHistoryInterface $passwordHistory): void;

}