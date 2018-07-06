<?php


namespace Despark\Bundle\PasswordPolicyBundle\Model;


interface PasswordHistoryInterface
{

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @param string $password
     */
    public function setPassword(string $password): void;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime;

    /**
     * @param \DateTime $dateTime
     * @return \DateTime|null
     */
    public function setCreatedAt(\DateTime $dateTime): void;

}