<?php


namespace Despark\PasswordPolicyBundle\Tests\Unit\Service;


use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Service\PasswordPolicyService;
use Despark\PasswordPolicyBundle\Service\PasswordPolicyServiceInterface;
use Despark\PasswordPolicyBundle\Tests\Unit\Mocks\PasswordHistoryMock;
use Despark\PasswordPolicyBundle\Tests\UnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PasswordPolicyServiceTest extends UnitTestCase
{
    /**
     * @var \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|\Mockery\Mock
     */
    private $entityMock;
    /**
     * @var PasswordPolicyServiceInterface|\Mockery\Mock
     */
    private $passwordPolicyServiceMock;
    /**
     * @var \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface|\Mockery\Mock
     */
    private $passwordEncoderFactoryMock;

    /**
     *
     */
    protected function setUp()
    {
        $this->passwordEncoderFactoryMock = \Mockery::mock(EncoderFactoryInterface::class);
        $this->passwordPolicyServiceMock = \Mockery::mock(PasswordPolicyService::class, [
            $this->passwordEncoderFactoryMock,
        ])->makePartial();

        $this->entityMock = \Mockery::mock(HasPasswordPolicyInterface::class);

    }

    public function testGetHistoryByPasswordMatch()
    {
        $encoderMock = \Mockery::mock(PasswordEncoderInterface::class);
        $encoderMock->shouldReceive('isPasswordValid')
                    ->twice()
                    ->andReturn(false, true);

        $this->passwordPolicyServiceMock->shouldReceive('getEncoder')
                                        ->once()
                                        ->withArgs([$this->entityMock])
                                        ->andReturn($encoderMock);

        $history[] = $this->makePasswordHistoryMock();
        $history[] = $this->makePasswordHistoryMock();

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->once()
                         ->andReturn(new ArrayCollection($history));

        $actual = $this->passwordPolicyServiceMock->getHistoryByPassword('pwd', $this->entityMock);
        $this->assertEquals($history[1], $actual);
    }

    public function testGetHistoryByPasswordNoMatch()
    {
        $encoderMock = \Mockery::mock(PasswordEncoderInterface::class);
        $encoderMock->shouldReceive('isPasswordValid')
                    ->twice()
                    ->andReturn(false, false);

        $this->passwordPolicyServiceMock->shouldReceive('getEncoder')
                                        ->once()
                                        ->withArgs([$this->entityMock])
                                        ->andReturn($encoderMock);


        $history[] = $this->makePasswordHistoryMock();
        $history[] = $this->makePasswordHistoryMock();

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->once()
                         ->andReturn(new ArrayCollection($history));

        $actual = $this->passwordPolicyServiceMock->getHistoryByPassword('pwd', $this->entityMock);
        $this->assertNull($actual);
    }

    public function testGetHistoryByPasswordEmptyHistory()
    {
        $encoderMock = \Mockery::mock(PasswordEncoderInterface::class);
        $encoderMock->shouldNotReceive('isPasswordValid');

        $this->passwordPolicyServiceMock->shouldReceive('getEncoder')
                                        ->once()
                                        ->withArgs([$this->entityMock])
                                        ->andReturn($encoderMock);

        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->once()
                         ->andReturn(new ArrayCollection());


        $actual = $this->passwordPolicyServiceMock->getHistoryByPassword('pwd', $this->entityMock);
        $this->assertNull($actual);
    }

    /**
     * @return \Mockery\Mock|\Despark\PasswordPolicyBundle\Tests\Unit\Mocks\PasswordHistoryMock
     */
    private function makePasswordHistoryMock()
    {
        return \Mockery::mock(PasswordHistoryMock::class)
                       ->shouldReceive('getPassword')
                       ->once()
                       ->andReturn('pwd')
                       ->shouldReceive('getSalt')
                       ->andReturn(null)
                       ->getMock();
    }
}