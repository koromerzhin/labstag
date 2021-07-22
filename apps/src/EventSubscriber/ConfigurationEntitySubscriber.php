<?php

namespace Labstag\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Configuration;
use Labstag\Event\ConfigurationEntityEvent;
use Labstag\Repository\ConfigurationRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ConfigurationEntitySubscriber implements EventSubscriberInterface
{

    protected CacheInterface $cache;

    protected EntityManagerInterface $entityManager;

    protected LoggerInterface $logger;

    protected ConfigurationRepository $repository;

    protected SessionInterface $session;

    public function __construct(
        SessionInterface $session,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        ConfigurationRepository $repository,
        CacheInterface $cache
    )
    {
        $this->cache         = $cache;
        $this->entityManager = $entityManager;
        $this->repository    = $repository;
        $this->logger        = $logger;
        $this->session       = $session;
    }

    public static function getSubscribedEvents()
    {
        return [ConfigurationEntityEvent::class => 'onEvent'];
    }

    public function onEvent(ConfigurationEntityEvent $event): void
    {
        $this->cache->delete('configuration');
        $post = $event->getPost();
        $this->setRobotsTxt($post);
        $this->flushPostConfiguration($post);
    }

    protected function flushPostConfiguration(array $post): void
    {
        foreach ($post as $key => $value) {
            if ('_token' == $key) {
                continue;
            }

            $configuration = $this->repository->findOneBy(['name' => $key]);
            if (!$configuration instanceof Configuration) {
                $configuration = new Configuration();
                $configuration->setName($key);
            }

            if (in_array($key, ['meta', 'disclaimer'])) {
                $value = $value[0];
            }

            $configuration->setValue($value);
            $this->entityManager->persist($configuration);
        }

        $this->entityManager->flush();
        /** @var Session $session */
        $session = $this->session;
        $session->getFlashBag()->add('success', 'Données sauvegardé');
    }

    protected function setRobotsTxt(array $post): void
    {
        if (!isset($post['robotstxt'])) {
            return;
        }

        /** @var Session $session */
        $session = $this->session;
        try {
            $value = $post['robotstxt'];
            file_put_contents('robots.txt', $value);
            $msg = 'fichier robots.txt modifié';
            $this->logger->info($msg);
            $session->getFlashBag()->add('success', $msg);
        } catch (Exception $exception) {
            $errorMsg = sprintf(
                'Exception : Erreur %s dans %s L.%s : %s',
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getMessage()
            );
            $this->logger->error($errorMsg);
            $session->getFlashBag()->add('danger', $errorMsg);
        }
    }
}
