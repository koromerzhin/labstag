<?php

namespace Labstag\Service;

use Labstag\Entity\Configuration;
use Labstag\Entity\User;
use Labstag\Repository\ConfigurationRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DataService
{

    protected CacheInterface $cache;

    protected array $config = [];

    protected array $oauthActivated = [];

    protected ConfigurationRepository $repository;

    public function __construct(
        ConfigurationRepository $repository,
        CacheInterface $cache
    )
    {
        $this->cache      = $cache;
        $this->repository = $repository;
        $this->setData();
    }

    public function compute(ItemInterface $item): array
    {
        $item->expiresAfter(1800);

        return $this->getConfiguration();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getOauthActivated(?User $user = null): array
    {
        if (is_null($user)) {
            return $this->oauthActivated;
        }

        $oauthActivateds = $this->oauthActivated;
        $oauthUsers      = $user->getOauthConnectUsers();
        foreach ($oauthActivateds as $index => $oauthActivated) {
            $trouver = 0;
            foreach ($oauthUsers as $oauthUser) {
                if ($oauthUser->getName() == $oauthActivated['type']) {
                    $trouver = 1;
                    break;
                }
            }

            if (1 === $trouver) {
                unset($oauthActivateds[$index]);
            }
        }

        return $oauthActivateds;
    }

    protected function getConfiguration()
    {
        $data   = $this->repository->findAll();
        $config = [];
        /** @var Configuration $row */
        foreach ($data as $row) {
            $key          = $row->getName();
            $value        = $row->getValue();
            $config[$key] = $value;
        }

        return $config;
    }

    protected function setData(): void
    {
        $config = $this->cache->get(
            'configuration',
            [
                $this,
                'compute',
            ]
        );
        if (0 === count($config)) {
            $config = $this->getConfiguration();
        }

        $this->config = $config;

        $this->setOauth($config);
    }

    protected function setOauth(array $config): void
    {
        if (!isset($config['oauth']) || !is_array($config['oauth'])) {
            return;
        }

        $oauth = [];
        $data  = $config['oauth'];
        foreach ($data as $row) {
            if (1 == $row['activate']) {
                $type         = $row['type'];
                $oauth[$type] = $row;
            }
        }

        $this->oauthActivated = $oauth;
    }
}
