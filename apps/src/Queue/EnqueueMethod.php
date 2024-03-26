<?php

namespace Labstag\Queue;

use DateTimeInterface;
use Labstag\Queue\Message\AsyncMethodMessage;
use Labstag\Queue\Message\SyncMethodMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class EnqueueMethod
{
    public function __construct(protected MessageBusInterface $messageBus)
    {
    }

    public function async(
        string $service,
        string $method,
        array $params = [],
        ?DateTimeInterface $dateTime = null
    ): void
    {
        $stamps = [];
        // Le service doit être appelé avec un délai
        if ($dateTime instanceof DateTimeInterface) {
            $delay = 1000 * ($dateTime->getTimestamp() - time());
            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }

        $this->messageBus->dispatch(
            new AsyncMethodMessage($service, $method, $params),
            $stamps
        );
    }

    public function sync(
        string $service,
        string $method,
        array $params = [],
        ?DateTimeInterface $dateTime = null
    ): void
    {
        $stamps = [];
        // Le service doit être appelé avec un délai
        if ($dateTime instanceof DateTimeInterface) {
            $delay = 1000 * ($dateTime->getTimestamp() - time());
            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }

        $this->messageBus->dispatch(
            new SyncMethodMessage($service, $method, $params),
            $stamps
        );
    }
}
