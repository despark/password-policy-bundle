<?php


namespace Despark\PasswordPolicyBundle\Model;


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
     * @return null|string
     */
    public function getSalt(): ?string;

    /**
     * @param null|string $salt
     * @return mixed
     */
    public function setSalt(?string $salt);

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