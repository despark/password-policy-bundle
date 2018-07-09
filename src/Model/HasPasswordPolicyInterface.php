<?php


namespace Despark\PasswordPolicyBundle\Model;


use Doctrine\Common\Collections\Collection;

/**
 * Interface HasPasswordPolicyInterface
 * @package Despark\PasswordPolicyBundle\Model
 */
interface HasPasswordPolicyInterface
{
    /**
     * @return mixed
     */
    public function getId();

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
     * @param \Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface $passwordHistory
     */
    public function addPasswordHistory(PasswordHistoryInterface $passwordHistory): void;

    /**
     * @return mixed
     */
    public function getPassword();

    /**
     * @return string|null The salt
     */
    public function getSalt();

}