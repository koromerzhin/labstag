<?php

namespace Labstag\EventSubscriber;

use Labstag\Service\UserMailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class PhoneWorkflowSubscriber implements EventSubscriberInterface
{

    protected UserMailService $userMailService;

    public function __construct(UserMailService $userMailService)
    {
        $this->userMailService = $userMailService;
    }

    public static function getSubscribedEvents()
    {
        return ['workflow.phone.transition' => 'onTransition'];
    }

    public function onTransition(Event $event)
    {
        $transition = $event->getTransition();
        $name       = $transition->getName();
        switch ($name) {
            case 'submit':
                $this->transitionSubmit($event);
                break;
        }
    }

    public function transitionSubmit(Event $event)
    {
        $entity = $event->getSubject();
        $this->userMailService->checkNewPhone($entity->getRefuser(), $entity);
    }
}
