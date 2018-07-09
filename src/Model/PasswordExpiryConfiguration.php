<?php


namespace Despark\PasswordPolicyBundle\Model;


use Despark\PasswordPolicyBundle\Exceptions\RuntimeException;

class PasswordExpiryConfiguration
{

    /**
     * @var string
     */
    private $entityClass;
    /**
     * @var int
     */
    private $expiryDays;
    /**
     * @var string
     */
    private $lockRoute;
    /**
     * @var array
     */
    private $excludedRoutes;
    /**
     * @var array
     */
    private $lockedRouteParams;

    /**
     * PasswordExpiryConfiguration constructor.
     * @param string $class
     * @param int $expiryDays
     * @param string $lockRoute
     * @param array $lockedRouteParams
     * @param array $excludedRoutes
     */
    public function __construct(
        string $class,
        int $expiryDays,
        string $lockRoute,
        array $lockedRouteParams = [],
        array $excludedRoutes = []
    ) {
        if (!is_a($class, HasPasswordPolicyInterface::class, true)) {
            throw new RuntimeException(sprintf('Entity %s must implement %s interface', $class,
                HasPasswordPolicyInterface::class));
        }
        $this->entityClass = $class;
        $this->expiryDays = $expiryDays;
        $this->lockRoute = $lockRoute;
        $this->excludedRoutes = $excludedRoutes;
        $this->lockedRouteParams = $lockedRouteParams;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return int
     */
    public function getExpiryDays(): int
    {
        return $this->expiryDays;
    }

    /**
     * @return string
     */
    public function getLockRoute(): string
    {
        return $this->lockRoute;
    }

    /**
     * @return array
     */
    public function getExcludedRoutes(): array
    {
        return $this->excludedRoutes;
    }

    /**
     * @return array
     */
    public function getLockedRouteParams(): array
    {
        return $this->lockedRouteParams;
    }
}