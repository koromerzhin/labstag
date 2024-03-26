<?php

namespace Labstag\Event\Subscriber;

use Labstag\Entity\User;
use Labstag\Lib\EventSubscriberLib;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Transition;

class WorkflowSubscriber extends EventSubscriberLib
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.attachment.transition' => 'onTransition',
            'workflow.bookmark.transition'   => 'onTransition',
            'workflow.chapter.transition'    => 'onTransition',
            'workflow.edito.transition'      => 'onTransition',
            'workflow.email.transition'      => 'onTransition',
            'workflow.history.transition'    => 'onTransition',
            'workflow.memo.transition'       => 'onTransition',
            'workflow.phone.transition'      => 'onTransition',
            'workflow.post.transition'       => 'onTransition',
            'workflow.user.transition'       => 'onTransition',
        ];
    }

    public function onTransition(Event $event): void
    {
        /** @var Transition $transition */
        $transition = $event->getTransition();
        $name       = $transition->getName();
        if ('submit' == $name) {
            $this->transitionSubmit($event);

            return;
        }

        if ('passwordlost' == $name) {
            $this->transitionPasswordLost($event);

            return;
        }

        $this->transitionDisable($event);
    }

    public function transitionDisable(Event $event): void
    {
        unset($event);
    }

    public function transitionPasswordLost(Event $event): void
    {
        /** @var User $subject */
        $subject = $event->getSubject();
        $this->userMailService->lostPassword($subject);
        $this->sessionService->flashBagAdd(
            'success',
            $this->translator->trans('user.workflow.sendpasswordlink')
        );
    }

    public function transitionSubmit(Event $event): void
    {
        /** @var User $subject */
        $subject = $event->getSubject();
        if (User::class == $subject::class) {
            $this->userMailService->newUser($subject);
            $this->sessionService->flashBagAdd(
                'success',
                $this->translator->trans('user.workflow.new')
            );
        }
    }
}
