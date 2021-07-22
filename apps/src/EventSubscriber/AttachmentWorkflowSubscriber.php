<?php

namespace Labstag\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class AttachmentWorkflowSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['workflow.attachment.transition' => 'onTransition'];
    }

    public function onTransition(Event $event)
    {
        $transition = $event->getTransition();
        $name       = $transition->getName();
        switch ($name) {
            case 'submit':
                $this->transitionSubmit($event);
                break;
            case 'valider':
                $this->transitionValider($event);
                break;
            case 'annuler':
                $this->transitionAnnuler($event);
                break;
            case 'reenvoyer':
                $this->transitionReEnvoyer($event);
                break;
        }
    }

    public function transitionAnnuler(Event $event)
    {
        unset($event);
    }

    public function transitionReEnvoyer(Event $event)
    {
        unset($event);
    }

    public function transitionSubmit(Event $event)
    {
        unset($event);
    }

    public function transitionValider(Event $event)
    {
        unset($event);
    }
}
