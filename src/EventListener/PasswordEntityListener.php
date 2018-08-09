<?php


namespace Despark\PasswordPolicyBundle\EventListener;


use Despark\PasswordPolicyBundle\Exceptions\RuntimeException;
use Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface;
use Despark\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface;
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
     * @var \Despark\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface
     */
    private $passwordHistoryService;
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var array
     */
    private $processedNewEntities = [];

    /**
     * @var array
     */
    private $processedPasswords =[];

    /**
     * PasswordEntityListener constructor.
     * @param \Despark\PasswordPolicyBundle\Service\PasswordHistoryServiceInterface $passwordHistoryService
     * @param string $passwordField
     * @param string $passwordHistoryField
     * @param int $historyLimit
     * @param string $entityClass
     */
    public function __construct(
        PasswordHistoryServiceInterface $passwordHistoryService,
        string $passwordField,
        string $passwordHistoryField,
        int $historyLimit,
        string $entityClass
    ) {
        $this->passwordField = $passwordField;
        $this->passwordHistoryField = $passwordHistoryField;
        $this->historyLimit = $historyLimit;
        $this->passwordHistoryService = $passwordHistoryService;
        $this->entityClass = $entityClass;
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     * @throws \Despark\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (is_a($entity, $this->entityClass, true) && $entity instanceof HasPasswordPolicyInterface) {
                $changeSet = $uow->getEntityChangeSet($entity);

                if (array_key_exists($this->passwordField, $changeSet) && array_key_exists(0,
                        $changeSet[$this->passwordField])) {
                    $this->createPasswordHistory($em, $entity, $changeSet[$this->passwordField][0]);
                }

            }
        }
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Despark\PasswordPolicyBundle\Model\HasPasswordPolicyInterface $entity
     * @param string|null $oldPassword
     * @return \Despark\PasswordPolicyBundle\Model\PasswordHistoryInterface|null
     * @throws \Despark\PasswordPolicyBundle\Exceptions\RuntimeException
     */
    public function createPasswordHistory(
        EntityManagerInterface $em,
        HasPasswordPolicyInterface $entity,
        ?string $oldPassword
    ): ?PasswordHistoryInterface {
        if (is_null($oldPassword) || $oldPassword === '') {
            $oldPassword = $entity->getPassword();
        }

        if (!$oldPassword) {
            return null;
        }

        if(array_key_exists($oldPassword, $this->processedPasswords)){
            return null;
        }

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
        $history->setSalt($entity->getSalt());

        $entity->addPasswordHistory($history);

        $this->processedPasswords[$oldPassword] = $history;

        $stalePasswords = $this->passwordHistoryService->getHistoryItemsForCleanup($entity, $this->historyLimit);

        foreach ($stalePasswords as $stalePassword) {
            $em->remove($stalePassword);
        }

        $em->persist($history);

        $metadata = $em->getClassMetadata($historyClass);
        $uow->computeChangeSet($metadata, $history);

        $entity->setPasswordChangedAt(new \DateTime());
        // We need to recompute the change set so we won't trigger updates instead of inserts.
        $uow->recomputeSingleEntityChangeSet($entityMeta, $entity);

        return $history;
    }

}
