<?php

namespace Labstag\Service;

use Labstag\Entity\Configuration;
use Labstag\Entity\User;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\TemplateRepository;
use Labstag\Repository\UserRepository;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

class InstallService
{
    public function __construct(
        protected OauthService $oauthService,
        protected UserService $userService,
        protected RepositoryService $repositoryService,
        protected Environment $twigEnvironment,
        protected CacheInterface $cache,
        protected GroupeRepository $groupeRepository,
        protected ConfigurationRepository $configurationRepository,
        protected TemplateRepository $templateRepository,
        protected UserRepository $userRepository
    )
    {
    }

    public function config(): void
    {
        $config = $this->getData('config');
        $this->setOauth($config);
        foreach ($config as $key => $row) {
            $this->addConfig($key, $row);
        }

        $this->cache->delete('configuration');
    }

    public function getData(string $file): array
    {
        $file = dirname(__DIR__, 2).'/json/'.$file.'.json';
        $data = [];
        if (is_file($file)) {
            $data = json_decode(
                (string) file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }

    public function getEnv(): array
    {
        $file   = dirname(__DIR__, 2).'/.env';
        $data   = [];
        $dotenv = new Dotenv();
        if (is_file($file)) {
            $data = $dotenv->parse((string) file_get_contents($file));
        }

        ksort($data);

        return $data;
    }

    public function users(): void
    {
        $users   = $this->getData('user');
        $groupes = $this->groupeRepository->findAll();
        foreach ($users as $user) {
            $this->addUser($groupes, $user);
        }
    }

    protected function addConfig(
        string $key,
        mixed $value
    ): void
    {
        $search        = ['name' => $key];
        $configuration = $this->configurationRepository->findOneBy($search);
        if (!$configuration instanceof Configuration) {
            $configuration = new Configuration();
        }

        $configuration->setName($key);
        $configuration->setValue($value);

        $this->configurationRepository->save($configuration);
    }

    protected function addUser(
        array $groupes,
        array $dataUser
    ): void
    {
        $search = [
            'username' => $dataUser['username'],
        ];
        $user = $this->userRepository->findOneBy($search);
        if ($user instanceof User) {
            return;
        }

        $this->userService->create($groupes, $dataUser);
    }

    protected function setOauth(array &$data): void
    {
        $env   = $this->getEnv();
        $oauth = [];
        foreach ($env as $key => $val) {
            if (0 == substr_count((string) $key, 'OAUTH_')) {
                continue;
            }

            $code    = str_replace('OAUTH_', '', (string) $key);
            $code    = strtolower($code);
            $explode = explode('_', $code);
            $type    = $explode[0];
            $key     = $explode[1];
            if (!isset($oauth[$type])) {
                $activate = $this->oauthService->getActivedProvider($type);

                $oauth[$type] = [
                    'activate' => $activate,
                    'type'     => $type,
                ];
            }

            $oauth[$type][$key] = $val;
        }

        /** @var mixed $row */
        foreach ($oauth as $row) {
            $data['oauth'][] = $row;
        }
    }
}
