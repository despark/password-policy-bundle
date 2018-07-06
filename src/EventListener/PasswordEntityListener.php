<?php


namespace Despark\Bundle\PasswordPolicyBundle\EventListener;


use Despark\Bundle\PasswordPolicyBundle\Exceptions\RuntimeException;
use Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\Bundle\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Despark\Bundle\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;

class PasswordEntityListener
{
    /**
     * @var string
     */
    private $passwordField;
    /**
     * @var string
     */
    private $passwordHistoryField;
    /**
     * @var int
     */
    private $historyLimit;
    /**
     * @var \Despark\Bundle\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface
     */
    private $passwordHistoryService;

    /**
     * PasswordEntityListener constructor.
     * @param \Despark\Bundle\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface $passwordHistoryService
     * @param string $passwordField
     * @param string $passwordHistoryField
     * @param int $historyLimit
     */
    public function __construct(
        PasswordHistoryServiceInterface $passwordHistoryService,
        string $passwordField,
        string $passwordHistoryField,
        int $historyLimit
    ) {
        $this->passwordField = $passwordField;
        $this->passwordHistoryField = $passwordHistoryField;
        $this->historyLimit = $historyLimit;
        $this->passwordHistoryService = $passwordHistoryService;
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     * @throws \Despark\Bundle\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {
            if ($entity instanceof HasPasswordPolicyInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                if (array_key_exists($this->passwordField, $changeSet) && isset($changeSet[$this->passwordField][0])) {
                    $this->createPasswordHistory($em, $entity, $changeSet[$this->passwordField][0]);
                }

            }
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param string $oldPassword
     * @throws \Despark\Bundle\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    private function createPasswordHistory(
        EntityManagerInterface $em,
        HasPasswordPolicyInterface $entity,
        string $oldPassword
    ) {
        $uow = $em->getUnitOfWork();
        $entityMeta = $em->getClassMetadata(get_class($entity));

        $historyClass = $entityMeta->associationMappings[$this->passwordHistoryField]['targetEntity'];
        $mappedField = $entityMeta->associationMappings[$this->passwordHistoryField]['mappedBy'];

        $history = new $historyClass();

        if (!$history instanceof PasswordHistoryInterface) {
            throw new RuntimeException(sprintf('%s must implement %s', $historyClass,
                PasswordHistoryInterface::class));
        }

        $userSetter = 'set'.ucfirst($mappedField);

        if (!method_exists($history, $userSetter)) {
            throw new RuntimeException(sprintf('Cannot set user relation in password history class %s because method %s is missing',
                $historyClass, $userSetter));
        }

        $history->$userSetter($entity);
        $history->setPassword($oldPassword);
        $history->setCreatedAt(new \DateTime());

        $entity->addPasswordHistory($history);

        $this->passwordHistoryService->cleanupHistory($entity, $this->historyLimit);

        $em->persist($history);
        $uow->computeChangeSets();

        $entity->setPasswordChangedAt(new \DateTime());
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Despark\Bundle\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     */
    private function cleanupHistory(EntityManagerInterface $em, HasPasswordPolicyInterface $entity): void
    {
        $historyCollection = $entity->getPasswordHistory();

        $len = $historyCollection->count();
        if ($len > $this->historyLimit) {
            $historyArray = $historyCollection->toArray();

            usort($historyArray, function (PasswordHistoryInterface $a, PasswordHistoryInterface $b) {
                $aTs = $a->getCreatedAt()->format('U');
                $bTs = $b->getCreatedAt()->format('U');

                return $aTs - $bTs;
            });

            $historyForCleanup = array_slice($historyArray, $this->historyLimit);
            foreach ($historyForCleanup as $item) {
                $em->remove($item);
            }
        }
    }

}