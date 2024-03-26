<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Labstag\Entity\History;
use Labstag\Lib\EventListenerLib;
use Labstag\Service\HistoryService;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
class HistoryListener extends EventListenerLib
{
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

    private function logActivity(string $action, LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (!$object instanceof History) {
            return;
        }

        $this->logger->info($action.' '.$object::class);
        $this->verifMetas($object);
        $this->enqueueMethod->async(
            HistoryService::class,
            'process',
            [
                'fileDirectory' => $this->parameterBag->get('file_directory'),
                'historyId'     => $object->getId(),
                'all'           => false,
            ]
        );
    }
}
