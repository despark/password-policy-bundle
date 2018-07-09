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
     * PasswordExpiryListener constructor.
     * @param \Despark\PasswordPolicyBundle\Service\PasswordExpiryServiceInterface $passwordExpiryService
     */
    public function __construct(PasswordExpiryServiceInterface $passwordExpiryService, SessionInterface $session)
    {
        $this->passwordExpiryService = $passwordExpiryService;
        $this->session = $session;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
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
                $this->session->getFlashBag()->add('error', 'Your password expired. You need to change it');
            }
            $event->setResponse(new RedirectResponse($lockedUrl));
        }
    }


}