<?php

namespace Paprec\PublicBundle\EventListener;

use Paprec\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $user = null;
        if ($this->container->get('security.token_storage')->getToken()) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }

        $availableLocales = $this->container->getParameter('available_locales');
        $defaultLocale = $this->container->getParameter('default_locale');

        $request = $event->getRequest();

        if ($user && $user instanceof User) {
            $locale = $user->getLang();
        } else {
            $locale = $request->get('locale');
        }

        $locale = strtolower($locale);

        if (!in_array($locale, $availableLocales)) {
            $locale = $defaultLocale;
        }

        $request->setLocale($locale);
    }
}
