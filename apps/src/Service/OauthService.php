<?php

namespace Labstag\Service;

use AdamPaterson\OAuth2\Client\Provider\Slack;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\LinkedIn;
use Omines\OAuth2\Client\Provider\Gitlab;
use Rudolf\OAuth2\Client\Provider\Reddit;
use RuntimeException;
use Stevenmaguire\OAuth2\Client\Provider\Bitbucket;
use Stevenmaguire\OAuth2\Client\Provider\Dropbox;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Vertisan\OAuth2\Client\Provider\TwitchHelix;
use Wohali\OAuth2\Client\Provider\Discord;

class OauthService
{

    protected array $configProvider;

    protected array $oauthActivated;

    public function __construct(
        protected RouterInterface $router,
        protected DataService $dataService
    )
    {
        $this->oauthActivated = $this->dataService->getOauthActivated();
        $this->setConfigProvider();
    }

    public function getActivedProvider(?string $clientName): bool
    {
        if (is_null($clientName)) {
            return false;
        }

        return array_key_exists($clientName, $this->configProvider);
    }

    public function getConfigProvider(): array
    {
        return $this->configProvider;
    }

    public function getIdentity(array $data, ?string $oauth): ?string
    {
        if (is_null($oauth)) {
            return null;
        }

        $config = $this->getConfig($oauth);
        if (!array_key_exists('identity', $config)) {
            return null;
        }

        $identity = $config['identity'];
        if (!array_key_exists($identity, $data)) {
            return null;
        }

        return $data[$identity];
    }

    /**
     * @return int[]|string[]
     */
    public function getTypes(): array
    {
        return array_keys($this->configProvider);
    }

    public function ifConfigProviderEnable(string $clientName): bool
    {
        if (!isset($this->configProvider[$clientName])) {
            return false;
        }

        if (!isset($this->oauthActivated[$clientName])) {
            return false;
        }

        return $this->oauthActivated[$clientName]['activate'];
    }

    public function setConfigProvider(): void
    {
        $file = dirname(__DIR__, 2).'/json/oauth.json';
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

        $this->configProvider = $data;
    }

    /**
     * @return AbstractProvider|void
     */
    public function setProvider(?string $clientName)
    {
        if (is_null($clientName)) {
            return;
        }

        if ($this->ifConfigProviderEnable($clientName)) {
            return $this->initProvider($clientName);
        }
    }

    protected function generateProvider(
        string $clientName,
        string $url,
        array $oauth
    ): ?AbstractProvider
    {
        $params = [
            'clientId'     => $oauth['id'],
            'clientSecret' => $oauth['secret'],
            'redirectUri'  => $url,
        ];

        return $this->generateStandardProvider($clientName, $params);
    }

    protected function generateStandardProvider(string $clientName, array $params): ?AbstractProvider
    {
        $provider  = null;
        $functions = [
            'generateStandardProviderBitbucket',
            'generateStandardProviderDiscord',
            'generateStandardProviderDropbox',
            'generateStandardProviderGithub',
            'generateStandardProviderGitlab',
            'generateStandardProviderGoogle',
            'generateStandardProviderLinkedin',
            'generateStandardProviderReddit',
            'generateStandardProviderSlack',
            'generateStandardProviderTwitch',
        ];

        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            $provider = call_user_func_array($callable, [$clientName, $params, $provider]);
        }

        if (!$provider instanceof AbstractProvider) {
            $provider = null;
        }

        return $provider;
    }

    protected function generateStandardProviderBitbucket(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('bitbucket' == $clientName) ? new Bitbucket($params) : $provider;
    }

    protected function generateStandardProviderDiscord(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('discord' == $clientName) ? new Discord($params) : $provider;
    }

    protected function generateStandardProviderDropbox(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('dropbox' == $clientName) ? new Dropbox($params) : $provider;
    }

    protected function generateStandardProviderGithub(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('github' == $clientName) ? new Github($params) : $provider;
    }

    protected function generateStandardProviderGitlab(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('gitlab' == $clientName) ? new Gitlab($params) : $provider;
    }

    protected function generateStandardProviderGoogle(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('google' == $clientName) ? new Google($params) : $provider;
    }

    protected function generateStandardProviderlinkedin(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('linkedin' == $clientName) ? new LinkedIn($params) : $provider;
    }

    protected function generateStandardProviderReddit(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        $params = [...$params,
            'userAgent' => 'platform:appid:version, (by /u/username)',
            'scopes'    => [
                'identity',
                'read',
            ],
        ];

        return ('reddit' == $clientName) ? new Reddit($params) : $provider;
    }

    protected function generateStandardProviderSlack(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('slack' == $clientName) ? new Slack($params) : $provider;
    }

    protected function generateStandardProviderTwitch(
        string $clientName,
        array $params,
        ?AbstractProvider $provider
    ): ?AbstractProvider
    {
        return ('twitch' == $clientName) ? new TwitchHelix($params) : $provider;
    }

    protected function getConfig(string $clientName): array
    {
        return $this->configProvider[$clientName] ?? [];
    }

    protected function initProvider(string $clientName): AbstractProvider
    {
        $code  = strtoupper($clientName);
        $oauth = $this->oauthActivated[strtolower($code)];
        $url   = 'https:'.$this->router->generate(
            'connect_check',
            ['oauthCode' => $clientName],
            UrlGeneratorInterface::NETWORK_PATH
        );

        $provider = $this->generateProvider($clientName, $url, $oauth);
        if (!$provider instanceof AbstractProvider) {
            throw new RuntimeException('Provider not found');
        }

        return $provider;
    }
}
