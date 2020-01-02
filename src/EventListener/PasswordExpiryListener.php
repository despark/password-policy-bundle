<?php


namespace Despark\PasswordPolicyBundle\EventListener;


use Despark\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class PasswordExpiryListener
{

    /**
     * @var PasswordExpiryServiceInterface
     */
    private $passwordExpiryService;
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $errorMessageType;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * PasswordExpiryListener constructor.
     * @param \Despark\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface $passwordExpiryService
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param string $errorMessageType
     * @param string $errorMessage
     */
    public function __construct(
        PasswordExpiryServiceInterface $passwordExpiryService,
        SessionInterface $session,
        string $errorMessageType,
        string $errorMessage
    ) {
        $this->passwordExpiryService = $passwordExpiryService;
        $this->session = $session;
        $this->errorMessageType = $errorMessageType;
        $this->errorMessage = $errorMessage;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->get('_route');

        $lockedUrl = $this->passwordExpiryService->generateLockedRoute();
        $lockedPath = parse_url($lockedUrl, PHP_URL_PATH);

        if ($request->getPathInfo() === $lockedPath) {
            return;
        }

        if (!in_array($route, $this->passwordExpiryService->getExcludedRoutes())
            && $this->passwordExpiryService->isPasswordExpired()) {
            if ($this->session instanceof Session) {
                $this->session->getFlashBag()->add($this->errorMessageType, $this->errorMessage);
            }
            $event->setResponse(new RedirectResponse($lockedUrl));
        }
    }


}