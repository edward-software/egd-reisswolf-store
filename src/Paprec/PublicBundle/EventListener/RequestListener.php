<?php
namespace Paprec\PublicBundle\EventListener;

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

        $availableLocales = $this->container->getParameter('available_locales');
        $defaultLocale = $this->container->getParameter('default_locale');

        $request = $event->getRequest();

        $locale = $request->get('locale');

        $locale = strtoupper($locale);

        if(! in_array($locale, $availableLocales)) {
            $locale = $defaultLocale;
        }

        $request->setLocale($locale);
    }
}
