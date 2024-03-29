<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Labstag\Entity\Block;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Lib\EventListenerLib;
use Labstag\Repository\BlockRepository;

class BlockListener extends EventListenerLib
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

    private function execute(Block $block): void
    {
        $classentity = $this->blockService->getTypeEntity($block);
        /** @var BlockRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get($block::class);
        if (is_null($classentity)) {
            $repositoryLib->remove($block);

            return;
        }

        $entity = $this->blockService->getEntity($block);
        if (!is_null($entity)) {
            return;
        }

        /** @var EntityBlockInterface $entity */
        $entity = new $classentity();
        $entity->setBlock($block);

        $repositoryLib->save($entity);
    }

    private function logActivity(string $action, LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (!$object instanceof Block) {
            return;
        }

        $this->logger->info($action.' '.$object::class);
        $this->execute($object);
    }
}
