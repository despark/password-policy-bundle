<?php


namespace Despark\Bundle\PasswordPolicyBundle\Tests\\Unit\Service;


use Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\Bundle\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Despark\Bundle\PasswordPolicyBundle\Service\PasswordHistoryService;
use Despark\Bundle\PasswordPolicyBundle\Tests\\UnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PasswordHistoryServiceTest extends UnitTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface|\Mockery\Mock
     */
    protected $emMock;
    /**
     * @var \Despark\Bundle\PasswordPolicyBundle\Service\PasswordHistoryService|\Mockery\Mock
     */
    protected $historyService;
    /**
     * @var \Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface|\Mockery\Mock
     */
    protected $entityMock;

    protected function setUp()
    {
        $this->emMock = \Mockery::mock(EntityManagerInterface::class);
        $this->entityMock = \Mockery::mock(HasPasswordPolicyInterface::class);
        $this->historyService = \Mockery::mock(PasswordHistoryService::class, [$this->emMock])->makePartial();
    }

    public function testCleanupHistory()
    {

        $passwordHistory = $this->getDummyPasswordHistory();
        $this->entityMock->shouldReceive('getPasswordHistory')
                         ->andReturn($passwordHistory);


        $this->emMock->shouldReceive('remove')
                     ->times(3);

        $deletedItems = $this->historyService->cleanupHistory($this->entityMock, 3);

        $expectedItems = [
            $passwordHistory->offsetGet(7),
            $passwordHistory->offsetGet(8),
            $passwordHistory->offsetGet(9),
        ];

        $this->assertEquals($expectedItems, $deletedItems);
    }

    private function getDummyPasswordHistory(): ArrayCollection
    {
        $collection = new ArrayCollection();
        $time = time();

        for ($i = 0; $i < 10; $i++) {

            $time += $i * 100;

            $collection->add(\Mockery::mock(PasswordHistoryInterface::class)
                                     ->shouldReceive('getCreatedAt')
                                     ->andReturn((new \DateTime())->setTimestamp($time))
                                     ->getMock());
        }

        return $collection;
    }


}