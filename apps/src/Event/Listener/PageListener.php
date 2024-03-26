<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Labstag\Entity\Page;
use Labstag\Lib\EventListenerLib;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
class PageListener extends EventListenerLib
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
        if (!$object instanceof Page) {
            return;
        }

        $this->logger->info($action.' '.$object::class);
        $this->verifMetas($object);

        $page = $object->getParent();
        if (!is_null($page)) {
            return;
        }

        $object->setSlug('');
    }
}
