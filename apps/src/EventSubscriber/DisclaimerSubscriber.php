<?php

namespace Labstag\EventSubscriber;

use Labstag\Service\DataService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class DisclaimerSubscriber implements EventSubscriberInterface
{

    protected DataService $dataService;

    protected RouterInterface $router;

    public function __construct(
        RouterInterface $router,
        DataService $dataService
    )
    {
        $this->dataService = $dataService;
        $this->router      = $router;
    }

    public static function getSubscribedEvents()
    {
        return ['kernel.request' => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $state   = $this->disclaimerActivate($request);
        if (!$state) {
            return;
        }

        $event->setResponse(
            new RedirectResponse(
                $this->router->generate('disclaimer')
            )
        );
    }

    protected function disclaimerActivate(Request $request): bool
    {
        $config     = $this->dataService->getConfig();
        $controller = $request->attributes->get('_controller');
        $key        = 'disclaimer';
        $session    = $request->getSession();
        if (!isset($config[$key]) || !isset($config[$key])) {
            return false;
        }

        if (0 === substr_count($controller, 'Labstag')) {
            return false;
        }

        if (0 !== substr_count($controller, 'Controller\\Api')) {
            return false;
        }

        if (0 !== substr_count($controller, 'Controller\\Admin')) {
            return false;
        }

        if (0 !== substr_count($controller, 'SecurityController')) {
            return false;
        }

        $disclaimer = $config[$key];
        $activate   = (bool) $disclaimer['activate'];

        return 1 !== $session->get($key, 0) && true === $activate;
    }
}
