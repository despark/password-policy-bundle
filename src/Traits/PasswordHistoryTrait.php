<?php


namespace Despark\PasswordPolicyBundle\Traits;


use Doctrine\ORM\Mapping as ORM;

trait PasswordHistoryTrait
{

    /**
     * @var string
     * @ORM\Column(type="string")
     * @ORM\Id()
     */
    private $password;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $salt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function updatedTimestamps()
    {

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param null|string $salt
     */
    public function setSalt(?string $salt): void
    {
        $this->salt = $salt;
    }

}