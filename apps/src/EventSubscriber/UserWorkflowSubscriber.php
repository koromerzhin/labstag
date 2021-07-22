<?php

namespace Labstag\EventSubscriber;

use Labstag\Service\UserMailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\Event\Event;

class UserWorkflowSubscriber implements EventSubscriberInterface
{

    protected SessionInterface $session;

    protected UserMailService $userMailService;

    public function __construct(
        UserMailService $userMailService,
        SessionInterface $session
    )
    {
        $this->session         = $session;
        $this->userMailService = $userMailService;
    }

    public static function getSubscribedEvents()
    {
        return ['workflow.user.transition' => 'onTransition'];
    }

    public function onTransition(Event $event)
    {
        $transition = $event->getTransition();
        $name       = $transition->getName();
        switch ($name) {
            case 'submit':
                $this->transitionSubmit($event);
                break;
            case 'passwordlost':
                $this->transitionPasswordLost($event);
                break;
        }
    }

    public function transitionPasswordLost(Event $event)
    {
        $entity = $event->getSubject();
        $this->userMailService->lostPassword($entity);
        /** @var Session $session */
        $session = $this->session;
        $session->getFlashBag()->add(
            'success',
            'Demande de nouveau mot de passe envoyé'
        );
    }

    public function transitionSubmit(Event $event)
    {
        $entity = $event->getSubject();
        $this->userMailService->newUser($entity);
        /** @var Session $session */
        $session = $this->session;
        $session->getFlashBag()->add(
            'success',
            'Nouveau compte utilisateur créer'
        );
    }
}
