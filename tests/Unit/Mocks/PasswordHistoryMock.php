<?php


namespace Despark\PasswordPolicyBundle\Tests\Unit\Mocks;

use Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Despark\PasswordPolicyBundle\Traits\PasswordHistoryTrait;

/**
 * Class PasswordHistoryMock.
 * Mocked class
 */
class PasswordHistoryMock implements PasswordHistoryInterface
{
    use PasswordHistoryTrait;

    private $user;

    /**
     * @param $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}