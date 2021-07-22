<?php

namespace Labstag\EventSubscriber;

use Labstag\Entity\EmailUser;
use Labstag\Service\UserMailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class EmailWorkflowSubscriber implements EventSubscriberInterface
{

    protected UserMailService $userMailService;

    public function __construct(UserMailService $userMailService)
    {
        $this->userMailService = $userMailService;
    }

    public static function getSubscribedEvents()
    {
        return ['workflow.email.transition' => 'onTransition'];
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
        /** @var EmailUser $entity */
        $entity = $event->getSubject();
        $user   = $entity->getRefuser();
        if ($entity->getAdresse() == $user->getEmail()) {
            return;
        }

        $this->userMailService->checkNewMail($entity->getRefuser(), $entity);
    }
}
