<?php

namespace Labstag\EventSubscriber;

use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Entity\WorkflowGroupe;
use Labstag\Entity\WorkflowUser;
use Labstag\Lib\EventSubscriberLib;
use Symfony\Component\Workflow\Event\GuardEvent;

class WorkflowGuardSubscriber extends EventSubscriberLib
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return ['workflow.guard' => 'onWorkflowAttachmentGuard'];
    }

    public function onWorkflowAttachmentGuard(GuardEvent $guardEvent): void
    {
        $stategroupe = false;
        $stateuser = false;
        $token = $this->tokenStorage->getToken();
        $workflowName = $guardEvent->getWorkflowName();
        $name = $guardEvent->getTransition()->getName();
        $workflow = $this->workflowRepository->findOneBy(
            [
                'entity'     => $workflowName,
                'transition' => $name,
            ]
        );

        if (is_null($token)) {
            return;
        }

        /** @var  User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            $groupe = $this->groupeRepository->findOneBy(['code' => 'visiteur']);
            $workflowGroupe = $this->workflowGroupeRepository->findOneBy(
                [
                    'refgroupe'   => $groupe,
                    'refworkflow' => $workflow,
                ]
            );

            $stategroupe = ($workflowGroupe instanceof WorkflowGroupe) ? $workflowGroupe->getState() : $stategroupe;
            $guardEvent->setBlocked(!$stategroupe);

            return;
        }

        $groupe = $user->getRefgroupe();
        if (!$groupe instanceof Groupe || 'superadmin' === $groupe->getCode()) {
            return;
        }

        $workflowGroupe = $this->workflowGroupeRepository->findOneBy(
            [
                'refgroupe'   => $groupe,
                'refworkflow' => $workflow,
            ]
        );
        $stategroupe = ($workflowGroupe instanceof WorkflowGroupe) ? $workflowGroupe->getState() : $stategroupe;
        $workflowUser = $this->workflowUserRepository->findOneBy(
            [
                'refuser'     => $user,
                'refworkflow' => $workflow,
            ]
        );
        $stateuser = ($workflowUser instanceof WorkflowUser) ? $workflowUser->getState() : $stateuser;

        $guardEvent->setBlocked(!$stategroupe || !$stateuser);
    }
}
