<?php


namespace Despark\PasswordPolicyBundle\Service;


use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PasswordPolicyEnforcerService
{

    /**
     * @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * PasswordPolicyEnforcerService constructor.
     * @param \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $passwordEncoder
     */
    public function __construct(PasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $password
     * @param \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @return bool
     */
    public function isInHistory(string $password, HasPasswordPolicyInterface $entity): bool
    {
        $history = $entity->getPasswordHistory();

        foreach ($history as $passwordHistory) {
            if ($this->passwordEncoder->isPasswordValid(
                $passwordHistory->getPassword(),
                $password,
                $entity->getSalt()
            )) {
                return true;
            }
        }

        return false;
    }

}