<?php


namespace Despark\Bundle\PasswordPolicyBundle\EventListener;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class PasswordExpiryListener
{

    public function __construct(int $expiryDays) { }

    public function onKernelRequest(GetResponseEvent $event)
    {

    }

}