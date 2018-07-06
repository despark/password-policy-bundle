<?php


namespace Despark\Bundle\PasswordPolicyBundle\Service;


use Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\Bundle\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class PasswordHistoryService implements PasswordHistoryServiceInterface
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * PasswordHistoryService constructor.
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param int $historyLimit
     *
     * @return array Removed items
     */
    public function cleanupHistory(HasPasswordPolicyInterface $entity, int $historyLimit): array
    {
        $historyCollection = $entity->getPasswordHistory();

        $len = $historyCollection->count();
        $removedItems = [];

        if ($len > $historyLimit) {
            $historyArray = $historyCollection->toArray();

            usort($historyArray, function (PasswordHistoryInterface $a, PasswordHistoryInterface $b) {
                $aTs = $a->getCreatedAt()->format('U');
                $bTs = $b->getCreatedAt()->format('U');

                return $aTs - $bTs;
            });

            $historyForCleanup = array_slice($historyArray, $historyLimit);

            foreach ($historyForCleanup as $item) {
                $removedItems[] = $item;
                $this->em->remove($item);
            }
        }

        return $removedItems;
    }

}