<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Lib\EventListenerLib;
use Labstag\Lib\RepositoryLib;
use Labstag\Repository\ParagraphRepository;

class ParagraphListener extends EventListenerLib
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->logActivity('persist', $lifecycleEventArgs);
    }

    public function postRemove(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->logActivity('remove', $lifecycleEventArgs);
    }

    public function postUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->logActivity('update', $lifecycleEventArgs);
    }

    private function eventData(Paragraph $paragraph): void
    {
        $this->paragraphService->setData($paragraph);
    }

    private function init(Paragraph $paragraph): void
    {
        $classentity = $this->paragraphService->getTypeEntity($paragraph);
        /** @var ParagraphRepository $paragraphRepository */
        $paragraphRepository = $this->repositoryService->get($paragraph::class);
        if (is_null($classentity)) {
            $paragraphRepository->remove($paragraph);

            return;
        }

        $entity = $this->paragraphService->getEntity($paragraph);
        if (!is_null($entity)) {
            return;
        }

        /** @var EntityParagraphInterface $entity */
        $entity = new $classentity();
        /** @var RepositoryLib $repository */
        $repository = $this->repositoryService->get($entity::class);
        $entity->setParagraph($paragraph);

        $repository->save($entity);
    }

    private function logActivity(string $action, LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (!$object instanceof Paragraph) {
            return;
        }

        $this->logger->info($action.' '.$object::class);
        $this->init($object);
        $this->eventData($object);
    }
}
