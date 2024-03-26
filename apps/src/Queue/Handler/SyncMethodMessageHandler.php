<?php

namespace Labstag\Queue\Handler;

use Labstag\Queue\Message\SyncMethodMessage;
use Labstag\Service\BlockService;
use Labstag\Service\BookmarkService;
use Labstag\Service\HistoryService;
use Labstag\Service\ParagraphService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsMessageHandler]
class SyncMethodMessageHandler implements ServiceSubscriberInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function __invoke(SyncMethodMessage $syncMethodMessage): void
    {
        /** @var callable $callable */
        $callable = [
            $this->container->get($syncMethodMessage->getServiceName()),
            $syncMethodMessage->getMethod(),
        ];

        call_user_func_array($callable, $syncMethodMessage->getParams());
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedServices(): array
    {
        return [
            MailerInterface::class  => MailerInterface::class,
            BlockService::class     => BlockService::class,
            ParagraphService::class => ParagraphService::class,
            BookmarkService::class  => BookmarkService::class,
            HistoryService::class   => HistoryService::class,
        ];
    }
}
