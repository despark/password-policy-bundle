<?php


namespace Despark\PasswordPolicyBundle\Tests\Unit\Service;


use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;
use Despark\PasswordPolicyBundle\Service\PasswordExpiryService;
use Despark\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface;
use Despark\PasswordPolicyBundle\Tests\UnitTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PasswordExpiryServiceTest extends UnitTestCase
{
    /**
     * @var \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|\Mockery\Mock
     */
    protected $userMock;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface|\Mockery\Mock
     */
    protected $routerMock;

    /**
     * @var PasswordExpiryServiceInterface|\Mockery\Mock
     */
    private $passwordExpiryServiceMock;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage|\Mockery\Mock
     */
    private $tokenStorageMock;

    protected function setUp()
    {
        $this->tokenStorageMock = \Mockery::mock(TokenStorageInterface::class);
        $this->routerMock = \Mockery::mock(UrlGeneratorInterface::class);
        $this->userMock = \Mockery::mock(HasPasswordPolicyInterface::class);
        $this->passwordExpiryServiceMock = \Mockery::mock(PasswordExpiryService::class, [
            $this->tokenStorageMock,
            $this->routerMock,
        ])->makePartial();
    }

    /**
     * @throws \Despark\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    public function testIsPasswordExpired()
    {
        $expiredPassword = (new \DateTime())->modify('-100 days');
        $notExpiredPassword = (new \DateTime())->modify('-89 days');
        $this->userMock->shouldReceive('getPasswordChangedAt')
                       ->twice()
                       ->andReturn($expiredPassword, $notExpiredPassword);

        $tokenMock = \Mockery::mock(TokenInterface::class)
                             ->shouldReceive('getUser')
                             ->andReturn($this->userMock)
                             ->getMock();

        $this->tokenStorageMock->shouldReceive('getToken')
                               ->andReturn($tokenMock);

        $this->passwordExpiryServiceMock->addEntity(
            new PasswordExpiryConfiguration(get_class($this->userMock), 90, 'lock')
        );

        $this->assertTrue($this->passwordExpiryServiceMock->isPasswordExpired());
        $this->assertFalse($this->passwordExpiryServiceMock->isPasswordExpired());
    }

    public function testGenerateLockedRoute()
    {
        $tokenMock = \Mockery::mock(TokenInterface::class)
                             ->shouldReceive('getUser')
                             ->andReturn($this->userMock)
                             ->getMock();

        $this->tokenStorageMock->shouldReceive('getToken')
                               ->andReturn($tokenMock);

        $this->passwordExpiryServiceMock->addEntity(
            new PasswordExpiryConfiguration(get_class($this->userMock), 90, 'lock', ['id' => 1])
        );

        $this->routerMock->shouldReceive('generate')
                         ->withArgs(['lock', ['id' => 1, 'foo' => 'bar']])
                         ->andReturn('lock/1');

        $route = $this->passwordExpiryServiceMock->generateLockedRoute(null, ['foo' => 'bar']);

        $this->assertEquals('lock/1', $route);
    }

}