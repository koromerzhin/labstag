<?php

namespace Labstag\Service;

use Labstag\Entity\Attachment;
use Labstag\Entity\Bookmark;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\Email;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Phone;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\Interfaces\EntityInterface;
use Labstag\Lib\RepositoryLib;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowService
{

    private array $data = [];

    public function __construct(
        protected WorkflowInterface $attachmentStateMachine,
        protected WorkflowInterface $chapterStateMachine,
        protected WorkflowInterface $bookmarkStateMachine,
        protected WorkflowInterface $editoStateMachine,
        protected WorkflowInterface $emailStateMachine,
        protected WorkflowInterface $historyStateMachine,
        protected WorkflowInterface $memoStateMachine,
        protected WorkflowInterface $phoneStateMachine,
        protected WorkflowInterface $postStateMachine,
        protected WorkflowInterface $userStateMachine,
        protected RepositoryService $repositoryService
    )
    {
        $this->data = [
            Attachment::class => $this->attachmentStateMachine,
            Bookmark::class   => $this->bookmarkStateMachine,
            Edito::class      => $this->editoStateMachine,
            Email::class      => $this->emailStateMachine,
            History::class    => $this->historyStateMachine,
            Chapter::class    => $this->chapterStateMachine,
            Memo::class       => $this->memoStateMachine,
            Phone::class      => $this->phoneStateMachine,
            Post::class       => $this->postStateMachine,
            User::class       => $this->userStateMachine,
        ];
    }

    public function changeState(EntityInterface $entity, array $states): void
    {
        if (!$this->has($entity)) {
            return;
        }

        /** @var WorkflowInterface $workflow */
        $workflow = $this->get($entity);
        foreach ($states as $state) {
            if (!$workflow->can($entity, $state)) {
                continue;
            }

            $workflow->apply($entity, $state);
        }

        /** @var RepositoryLib $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->repositoryService->get($entity::class);
        $serviceEntityRepositoryLib->save($entity);
    }

    public function get(EntityInterface $entity): ?WorkflowInterface
    {
        $workflow = null;
        foreach ($this->data as $key => $state) {
            if ($entity::class == $key) {
                $workflow = $state;

                break;
            }
        }

        return $workflow;
    }

    public function has(EntityInterface $entity): bool
    {
        return !is_null($this->get($entity));
    }

    public function init(EntityInterface $entity): void
    {
        if (!$this->has($entity)) {
            return;
        }

        /** @var WorkflowInterface $workflow */
        $workflow    = $this->get($entity);
        $definition  = $workflow->getDefinition();
        $transitions = $definition->getTransitions();
        foreach ($transitions as $transition) {
            $name = $transition->getName();
            if (!$workflow->can($entity, $name)) {
                continue;
            }

            $workflow->apply($entity, $name);
            /** @var RepositoryLib $repository */
            $repository = $this->repositoryService->get($entity::class);
            $repository->save($entity);

            break;
        }
    }
}
