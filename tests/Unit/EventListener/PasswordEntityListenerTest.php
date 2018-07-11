<?php


namespace Despark\PasswordPolicyBundle\Tests\Unit\EventListener;


use Despark\PasswordPolicyBundle\EventListener\PasswordEntityListener;
use Despark\PasswordPolicyBundle\Exceptions\RuntimeException;
use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Despark\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface;
use Despark\PasswordPolicyBundle\Tests\Unit\Mocks\PasswordHistoryMock;
use Despark\PasswordPolicyBundle\Tests\UnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;

class PasswordEntityListenerTest extends UnitTestCase
{

    /**
     * @var \Despark\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface|\Mockery\Mock
     */
    private $passwordHistoryServiceMock;

    /**
     * @var \Despark\PasswordPolicyBundle\EventListener\PasswordEntityListener|\Mockery\Mock
     */
    private $passwordEntityListener;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface|\Mockery\Mock
     */
    private $emMock;
    /**
     * @var \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|\Mockery\Mock
     */
    private $entityMock;
    /**
     * @var \Doctrine\ORM\UnitOfWork|\Mockery\Mock
     */
    private $uowMock;

    protected function setUp()
    {
        $this->passwordHistoryServiceMock = \Mockery::mock(PasswordHistoryServiceInterface::class);

        $this->emMock = \Mockery::mock(EntityManagerInterface::class);

        $this->entityMock = \Mockery::mock(HasPasswordPolicyInterface::class);

        $this->uowMock = \Mockery::mock(UnitOfWork::class);

        $this->passwordEntityListener = \Mockery::mock(PasswordEntityListener::class, [
            $this->passwordHistoryServiceMock,
            'password',
            'passwordHistory',
            '3',
            get_class($this->entityMock),
        ])->makePartial();
    }

    /**
     * @throws \Despark\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    public function testOnFlushUpdates()
    {
        $this->uowMock->shouldReceive('getScheduledEntityInsertions')
                      ->once()
                      ->andReturn([]);

        $this->uowMock->shouldReceive('getScheduledEntityUpdates')
                      ->once()
                      ->andReturn([
                          $this->entityMock,
                      ]);
        $this->uowMock->shouldReceive('getEntityChangeSet')
                      ->once()
                      ->with($this->entityMock)
                      ->andReturn([
                          'password' => [
                              'pwd_1',
                              'pwd_2',
                          ],
                      ]);

        $this->passwordEntityListener->shouldReceive('createPasswordHistory')
                                     ->once()
                                     ->withArgs([$this->emMock, $this->entityMock, 'pwd_1']);

        $this->emMock->shouldReceive('getUnitOfWork')
                     ->andReturn($this->uowMock);

        $event = new OnFlushEventArgs($this->emMock);

        $this->passwordEntityListener->onFlush($event);

        $this->assertTrue(true);
    }

    public function testOnFlushInserts()
    {
        $this->entityMock->shouldReceive('getPassword')
                         ->andReturn('pwd');

        $this->uowMock->shouldReceive('getScheduledEntityInsertions')
                      ->once()
                      ->andReturn([
                          $this->entityMock,
                      ]);

        $this->uowMock->shouldReceive('getScheduledEntityUpdates')
                      ->once()
                      ->andReturn([]);

        $this->uowMock->shouldNotReceive('getEntityChangeSet');

        $this->passwordEntityListener->shouldReceive('createPasswordHistory')
                                     ->once()
                                     ->withArgs([$this->emMock, $this->entityMock, 'pwd']);

        $this->emMock->shouldReceive('getUnitOfWork')
                     ->andReturn($this->uowMock);

        $event = new OnFlushEventArgs($this->emMock);

        $this->passwordEntityListener->onFlush($event);

        $this->assertTrue(true);
    }

    public function testCreatePasswordHistory()
    {
        $this->uowMock->shouldReceive('computeChangeSets')
                      ->once();

        $this->emMock->shouldReceive('getUnitOfWork')
                     ->once()
                     ->andReturn($this->uowMock);

        $classMetadata = new ClassMetadata('foo');

        $classMetadata->associationMappings['passwordHistory']['targetEntity'] = PasswordHistoryMock::class;
        $classMetadata->associationMappings['passwordHistory']['mappedBy'] = 'user';

        $this->emMock->shouldReceive('getClassMetadata')
                     ->once()
                     ->withArgs([get_class($this->entityMock)])
                     ->andReturn($classMetadata);

        $this->entityMock->shouldReceive('addPasswordHistory')
                         ->once();

        $this->entityMock->shouldReceive('getSalt')
                         ->andReturn('salt');

        $pwdHistoryMock = \Mockery::mock(PasswordHistoryMock::class);
        $this->passwordHistoryServiceMock->shouldReceive('getHistoryItemsForCleanup')
                                         ->once()
                                         ->withArgs([$this->entityMock, 3])
                                         ->andReturn([$pwdHistoryMock]);

        $this->emMock->shouldReceive('remove')
                     ->once()
                     ->with($pwdHistoryMock);

        $this->emMock->shouldReceive('persist')
                     ->once();

        $this->entityMock->shouldReceive('setPasswordChangedAt')
                         ->once();

        $history = $this->passwordEntityListener->createPasswordHistory($this->emMock, $this->entityMock, 'old_pwd');

        $this->assertEquals($history->getPassword(), 'old_pwd');
        $this->assertNotNull($history->getCreatedAt());
        $this->assertEquals($this->entityMock, $history->getUser());
        $this->assertEquals('salt', $history->getSalt());
    }

    public function testCreatePasswordHistoryNullPassword()
    {
        $this->uowMock->shouldReceive('computeChangeSets')
                      ->once();

        $this->emMock->shouldReceive('getUnitOfWork')
                     ->once()
                     ->andReturn($this->uowMock);

        $classMetadata = new ClassMetadata('foo');

        $classMetadata->associationMappings['passwordHistory']['targetEntity'] = PasswordHistoryMock::class;
        $classMetadata->associationMappings['passwordHistory']['mappedBy'] = 'user';

        $this->emMock->shouldReceive('getClassMetadata')
                     ->once()
                     ->withArgs([get_class($this->entityMock)])
                     ->andReturn($classMetadata);

        $this->entityMock->shouldReceive('addPasswordHistory')
                         ->once();

        $this->entityMock->shouldReceive('getSalt')
                         ->andReturn('salt');

        $pwdHistoryMock = \Mockery::mock(PasswordHistoryMock::class);
        $this->passwordHistoryServiceMock->shouldReceive('getHistoryItemsForCleanup')
                                         ->once()
                                         ->withArgs([$this->entityMock, 3])
                                         ->andReturn([$pwdHistoryMock]);

        $this->emMock->shouldReceive('remove')
                     ->once()
                     ->with($pwdHistoryMock);

        $this->emMock->shouldReceive('persist')
                     ->once();

        $this->entityMock->shouldReceive('setPasswordChangedAt')
                         ->once();

        $this->entityMock->shouldReceive('getPassword')
                         ->twice()
                         ->andReturnValues(['pwd', null]);

        $history = $this->passwordEntityListener->createPasswordHistory($this->emMock, $this->entityMock, null);

        $this->assertEquals($history->getPassword(), 'pwd');
        $this->assertNotNull($history->getCreatedAt());
        $this->assertEquals($this->entityMock, $history->getUser());
        $this->assertEquals('salt', $history->getSalt());

        $this->assertNull($this->passwordEntityListener->createPasswordHistory($this->emMock, $this->entityMock, null));
    }

    public function testCreatePasswordHistoryBadInstance()
    {
        $this->uowMock->shouldNotReceive('computeChangeSets');

        $this->emMock->shouldNotReceive('getUnitOfWork')
                     ->once()
                     ->andReturn($this->uowMock);

        $classMetadata = new ClassMetadata('foo');

        $classMetadata->associationMappings['passwordHistory']['targetEntity'] = static::class;
        $classMetadata->associationMappings['passwordHistory']['mappedBy'] = 'user';

        $this->emMock->shouldReceive('getClassMetadata')
                     ->once()
                     ->withArgs([get_class($this->entityMock)])
                     ->andReturn($classMetadata);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(static::class.' must implement '.PasswordHistoryInterface::class);

        $this->passwordEntityListener->createPasswordHistory($this->emMock, $this->entityMock, 'old_pwd');
    }

    public function testCreatePasswordHistoryBadSetter()
    {
        $this->uowMock->shouldNotReceive('computeChangeSets');

        $this->emMock->shouldNotReceive('getUnitOfWork')
                     ->once()
                     ->andReturn($this->uowMock);

        $classMetadata = new ClassMetadata('foo');

        $classMetadata->associationMappings['passwordHistory']['targetEntity'] = PasswordHistoryMock::class;
        $classMetadata->associationMappings['passwordHistory']['mappedBy'] = 'foo';

        $this->emMock->shouldReceive('getClassMetadata')
                     ->once()
                     ->withArgs([get_class($this->entityMock)])
                     ->andReturn($classMetadata);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf("Cannot set user relation in password history class %s because method %s is missing",
                PasswordHistoryMock::class, 'setFoo'
            ));

        $this->passwordEntityListener->createPasswordHistory($this->emMock, $this->entityMock, 'old_pwd');
    }
}
