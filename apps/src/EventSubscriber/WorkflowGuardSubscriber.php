<?php

namespace Labstag\EventSubscriber;

use Labstag\Entity\User;
use Labstag\Entity\WorkflowGroupe;
use Labstag\Entity\WorkflowUser;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\WorkflowGroupeRepository;
use Labstag\Repository\WorkflowRepository;
use Labstag\Repository\WorkflowUserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class WorkflowGuardSubscriber implements EventSubscriberInterface
{

    protected GroupeRepository $groupeRepo;

    protected TokenStorageInterface $token;

    protected WorkflowGroupeRepository $workflowGroupeRepo;

    protected WorkflowRepository $workflowRepo;

    protected WorkflowUserRepository $workflowUserRepo;

    public function __construct(
        TokenStorageInterface $token,
        WorkflowUserRepository $workflowUserRepo,
        GroupeRepository $groupeRepo,
        WorkflowRepository $workflowRepo,
        WorkflowGroupeRepository $workflowGroupeRepo
    )
    {
        $this->workflowRepo       = $workflowRepo;
        $this->groupeRepo         = $groupeRepo;
        $this->workflowUserRepo   = $workflowUserRepo;
        $this->workflowGroupeRepo = $workflowGroupeRepo;
        $this->token              = $token;
    }

    public static function getSubscribedEvents()
    {
        return ['workflow.guard' => 'onWorkflowAttachmentGuard'];
    }

    public function onWorkflowAttachmentGuard(GuardEvent $event)
    {
        $stategroupe = false;
        $stateuser   = false;
        $token       = $this->token->getToken();
        $name        = $event->getWorkflowName();
        $transition  = $event->getTransition()->getName();
        $workflow    = $this->workflowRepo->findOneBy(
            [
                'entity'     => $name,
                'transition' => $transition,
            ]
        );

        if (is_null($token)) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            $groupe         = $this->groupeRepo->findOneBy(['code' => 'visiteur']);
            $workflowGroupe = $this->workflowGroupeRepo->findOneBy(
                [
                    'refgroupe'   => $groupe,
                    'refworkflow' => $workflow,
                ]
            );

            $stategroupe = ($workflowGroupe instanceof WorkflowGroupe) ? $workflowGroupe->getState() : $stategroupe;
            $event->setBlocked(!$stategroupe);

            return;
        }

        $groupe = $user->getRefgroupe();
        if ('superadmin' === $groupe->getCode()) {
            return;
        }

        $workflowGroupe = $this->workflowGroupeRepo->findOneBy(
            [
                'refgroupe'   => $groupe,
                'refworkflow' => $workflow,
            ]
        );
        $stategroupe    = ($workflowGroupe instanceof WorkflowGroupe) ? $workflowGroupe->getState() : $stategroupe;
        $workflowUser   = $this->workflowUserRepo->findOneBy(
            [
                'refuser'     => $user,
                'refworkflow' => $workflow,
            ]
        );
        $stategroupe    = ($workflowUser instanceof WorkflowUser) ? $workflowUser->getState() : $stategroupe;

        $event->setBlocked(!$stategroupe || !$stateuser);
    }
}
