<?php

namespace Labstag\EventSubscriber;

use Labstag\Repository\GroupeRepository;
use Labstag\Service\GuardService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GuardRouterSubscriber implements EventSubscriberInterface
{

    protected GroupeRepository $groupeRepository;

    protected GuardService $guardService;

    protected RouterInterface $router;

    protected SessionInterface $session;

    protected TokenStorageInterface $token;

    public function __construct(
        SessionInterface $session,
        RouterInterface $router,
        TokenStorageInterface $token,
        GroupeRepository $groupeRepository,
        GuardService $guardService
    )
    {
        $this->groupeRepository = $groupeRepository;
        $this->session          = $session;
        $this->token            = $token;
        $this->router           = $router;
        $this->guardService     = $guardService;
    }

    public static function getSubscribedEvents()
    {
        return ['kernel.request' => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route   = $request->attributes->get('_route');
        $token   = $this->token->getToken();
        $acces   = $this->guardService->guardRoute($route, $token);
        if ($acces) {
            return;
        }

        /** @var Session $session */
        $session = $this->session;
        $session->getFlashBag()->add(
            'note',
            "Vous n'avez pas les droits nécessaires"
        );
        throw new AccessDeniedException();
    }
}
