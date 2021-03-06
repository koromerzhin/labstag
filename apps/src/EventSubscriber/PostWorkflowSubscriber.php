<?php

namespace Labstag\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class PostWorkflowSubscriber implements EventSubscriberInterface
{
    public function onTransition(Event $event)
    {
        $transition = $event->getTransition();
        $name       = $transition->getName();
        switch ($name) {
            case 'submit':
                $this->transitionSubmit($event);
                break;
            case 'relire':
                $this->transitionRelire($event);
                break;
            case 'corriger':
                $this->transitionCorriger($event);
                break;
            case 'publier':
                $this->transitionPublier($event);
                break;
            case 'rejeter':
                $this->transitionRejeter($event);
                break;
        }
    }

    public function transitionSubmit(Event $event)
    {
        unset($event);
    }

    public function transitionRelire(Event $event)
    {
        unset($event);
    }

    public function transitionCorriger(Event $event)
    {
        unset($event);
    }

    public function transitionPublier(Event $event)
    {
        unset($event);
    }

    public function transitionRejeter(Event $event)
    {
        unset($event);
    }

    public static function getSubscribedEvents()
    {
        return ['workflow.post.transition' => 'onTransition'];
    }
}
