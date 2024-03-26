<?php

namespace Labstag\Event\Subscriber;

use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Entity\Workflow;
use Labstag\Entity\WorkflowGroupe;
use Labstag\Entity\WorkflowUser;
use Labstag\Lib\EventSubscriberLib;
use Labstag\Lib\RepositoryLib;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\WorkflowGroupeRepository;
use Labstag\Repository\WorkflowRepository;
use Labstag\Repository\WorkflowUserRepository;
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
        $token        = $this->tokenStorage->getToken();
        $workflowName = $guardEvent->getWorkflowName();
        $name         = $guardEvent->getTransition()->getName();
        /** @var WorkflowRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Workflow::class);
        /** @var GroupeRepository $repositoryGroupe */
        $repositoryGroupe = $this->repositoryService->get(Groupe::class);
        /** @var WorkflowGroupeRepository $repositoryWorkflowGroupe */
        $repositoryWorkflowGroupe = $this->repositoryService->get(WorkflowGroupe::class);
        /** @var WorkflowUserRepository $repositoryWorkflowUser */
        $repositoryWorkflowUser = $this->repositoryService->get(WorkflowUser::class);
        $testRepo               = $this->testrepo(
            $repositoryLib,
            $repositoryGroupe,
            $repositoryWorkflowGroupe,
            $repositoryWorkflowUser
        );

        $workflow = $repositoryLib->findOneBy(
            [
                'entity'     => $workflowName,
                'transition' => $name,
            ]
        );
        if (!$testRepo || is_null($token) || !$workflow instanceof Workflow) {
            return;
        }

        /** @var User $user */
        $user        = $token->getUser();
        $groupe      = $this->setGroupe($repositoryGroupe, $user);
        $stateGroupe = $this->getStateGroupe(
            $repositoryWorkflowGroupe,
            $groupe,
            $workflow
        );

        $stateUser = $this->getStateUser(
            $repositoryWorkflowUser,
            $groupe,
            $user,
            $workflow
        );

        $guardEvent->setBlocked(!$stateGroupe || !$stateUser);
    }

    private function getStateGroupe(
        WorkflowGroupeRepository $workflowGroupeRepository,
        Groupe $groupe,
        ?Workflow $workflow = null
    ): bool
    {
        if ('superadmin' === $groupe->getCode()) {
            return true;
        }

        $workflowGroupe = $workflowGroupeRepository->findOneBy(
            [
                'refgroupe'   => $groupe,
                'refworkflow' => $workflow,
            ]
        );

        $test = ($workflowGroupe instanceof WorkflowGroupe) ? $workflowGroupe->getState() : false;
        if (!is_bool($test)) {
            return true;
        }

        return $test;
    }

    private function getStateUser(
        WorkflowUserRepository $workflowUserRepository,
        Groupe $groupe,
        ?User $user = null,
        ?Workflow $workflow = null
    ): bool
    {
        $code = $groupe->getCode();
        if (is_null($code)) {
            return false;
        }

        if (!$user instanceof User || 'superadmin' === $code) {
            return true;
        }

        $workflowUser = $workflowUserRepository->findOneBy(
            [
                'refuser'     => $user,
                'refworkflow' => $workflow,
            ]
        );

        $test = ($workflowUser instanceof WorkflowUser) ? $workflowUser->getState() : false;

        if (!is_bool($test)) {
            return true;
        }

        return $test;
    }

    private function setGroupe(
        GroupeRepository $groupeRepository,
        ?User $user = null
    ): Groupe
    {
        $groupe = $groupeRepository->findOneBy(['code' => 'visiteur']);
        if (!$groupe instanceof Groupe) {
            $groupe = new Groupe();
            $groupe->setCode('visiteur');
            $groupe->setName('Visiteur');
            $groupeRepository->save($groupe);
        }

        if (!$user instanceof User) {
            return $groupe;
        }

        $refgroupe = $user->getRefgroupe();
        if (!$refgroupe instanceof Groupe) {
            return $groupe;
        }

        return $refgroupe;
    }

    private function testrepo(
        ?WorkflowRepository $workflowRepository = null,
        ?GroupeRepository $groupeRepository = null,
        ?WorkflowGroupeRepository $workflowGroupeRepository = null,
        ?WorkflowUserRepository $workflowUserRepository = null
    ): bool
    {
        $tab = [
            $workflowRepository,
            $groupeRepository,
            $workflowGroupeRepository,
            $workflowUserRepository,
        ];

        $test = true;
        foreach ($tab as $key) {
            if (!$key instanceof RepositoryLib) {
                $test = false;

                break;
            }
        }

        return $test;
    }
}
