<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Labstag\Entity\Configuration;
use Labstag\Lib\EventListenerLib;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
class ConfigurationListener extends EventListenerLib
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

    private function execute(Configuration $configuration): void
    {
        $this->cache->delete('configuration');
        $this->setRobotsTxt($configuration);
    }

    private function logActivity(string $action, LifecycleEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (!$object instanceof Configuration) {
            return;
        }

        $this->logger->info($action.' '.$object::class);
        $this->execute($object);
    }

    private function setRobotsTxt(Configuration $configuration): void
    {
        if ('robotstxt' != $configuration->getName()) {
            return;
        }

        try {
            $value = $configuration->getValue();
            $file  = 'public/robots.txt';
            if (is_file($file)) {
                unlink($file);
            }

            file_put_contents($file, $value);
            $msg = $this->translator->trans('admin.robotstxt.file', ['%file%' => $file]);
            $this->logger->info($msg);
            $this->sessionService->flashBagAdd('success', $msg);
        } catch (Exception $exception) {
            $this->errorService->set($exception);
        }
    }
}
