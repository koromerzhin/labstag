<?php

namespace Labstag\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Event\HistoryEntityEvent;
use Labstag\Queue\EnqueueMethod;
use Labstag\Service\HistoryService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HistoryEntitySubscriber implements EventSubscriberInterface
{

    protected ParameterBagInterface $containerBag;

    protected EnqueueMethod $enqueue;

    protected EntityManagerInterface $entityManager;

    public function __construct(
        ParameterBagInterface $containerBag,
        EntityManagerInterface $entityManager,
        EnqueueMethod $enqueue
    )
    {
        $this->containerBag  = $containerBag;
        $this->enqueue       = $enqueue;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [HistoryEntityEvent::class => 'onHistoryEntityEvent'];
    }

    public function onHistoryEntityEvent(HistoryEntityEvent $event): void
    {
        $entity = $event->getNewEntity();
        $this->enqueue->enqueue(
            HistoryService::class,
            'process',
            [
                'fileDirectory' => $this->getParameter('file_directory'),
                'historyId'     => $entity->getId(),
                'all'           => false,
            ]
        );
    }

    protected function getParameter(string $name)
    {
        return $this->containerBag->get($name);
    }
}