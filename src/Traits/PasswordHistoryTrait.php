<?php


namespace Despark\Bundle\PasswordPolicyBundle\Traits;


trait PasswordHistoryTrait
{

    /**
     * @var string
     * @ORM\Column(type="string")
     * @ORM\Id()
     */
    private $password;

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

}