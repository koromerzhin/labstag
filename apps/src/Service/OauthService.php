<?php

namespace Labstag\Service;

use AdamPaterson\OAuth2\Client\Provider\Slack;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\LinkedIn;
use Omines\OAuth2\Client\Provider\Gitlab;
use Rudolf\OAuth2\Client\Provider\Reddit;
use Stevenmaguire\OAuth2\Client\Provider\Bitbucket;
use Stevenmaguire\OAuth2\Client\Provider\Dropbox;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Vertisan\OAuth2\Client\Provider\TwitchHelix;
use Wohali\OAuth2\Client\Provider\Discord;

class OauthService
{

    protected array $configProvider;

    protected DataService $dataService;

    protected array $oauthActivated;

    /**
     * @var Router|RouterInterface
     */
    protected $router;

    public function __construct(
        RouterInterface $router,
        DataService $dataService
    )
    {
        $this->dataService    = $dataService;
        $this->oauthActivated = $this->dataService->getOauthActivated();
        $this->router         = $router;
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

    /**
     * @param mixed|null $data
     *
     * @return mixed|void
     */
    public function getIdentity($data, ?string $oauth)
    {
        if (is_null($oauth)) {
            return;
        }

        $config = $this->getConfig($oauth);
        if (!array_key_exists('identity', $config)) {
            return;
        }

        $identity = $config['identity'];
        if (!array_key_exists($identity, $data)) {
            return;
        }

        return $data[$identity];
    }

    public function getTypes(): array
    {
        $types = [];
        foreach (array_keys($this->configProvider) as $key) {
            $types[] = $key;
        }

        return $types;
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
        $file = __DIR__.'/../../json/oauth.json';
        $data = [];
        if (is_file($file)) {
            $data = json_decode(file_get_contents($file), true);
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

    protected function generateProvider($clientName, $url, $oauth)
    {
        $params   = [
            'clientId'     => $oauth['id'],
            'clientSecret' => $oauth['secret'],
            'redirectUri'  => $url,
        ];
        $provider = $this->generateStandardProvider($clientName, $params);
        if ('reddit' == $clientName) {
            $provider = new Reddit(
                [
                    'clientId'     => $oauth['id'],
                    'clientSecret' => $oauth['secret'],
                    'redirectUri'  => $url,
                    'userAgent'    => 'platform:appid:version, (by /u/username)',
                    'scopes'       => [
                        'identity',
                        'read',
                    ],
                ]
            );
        }

        return $provider;
    }

    protected function generateStandardProvider($clientName, $params)
    {
        $provider = null;
        $provider = $this->generateStandardProviderBitbucket($clientName, $params, $provider);
        $provider = $this->generateStandardProviderDiscord($clientName, $params, $provider);
        $provider = $this->generateStandardProviderDropbox($clientName, $params, $provider);
        $provider = $this->generateStandardProviderGithub($clientName, $params, $provider);
        $provider = $this->generateStandardProviderGitlab($clientName, $params, $provider);
        $provider = $this->generateStandardProviderGoogle($clientName, $params, $provider);
        $provider = $this->generateStandardProviderLinkedin($clientName, $params, $provider);
        $provider = $this->generateStandardProviderSlack($clientName, $params, $provider);
        $provider = $this->generateStandardProviderTwitch($clientName, $params, $provider);

        return $provider;
    }

    protected function generateStandardProviderBitbucket($clientName, $params, $provider)
    {
        $provider = ('bitbucket' == $clientName) ? new Bitbucket($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderDiscord($clientName, $params, $provider)
    {
        $provider = ('discord' == $clientName) ? new Discord($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderDropbox($clientName, $params, $provider)
    {
        $provider = ('dropbox' == $clientName) ? new Dropbox($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderGithub($clientName, $params, $provider)
    {
        $provider = ('github' == $clientName) ? new Github($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderGitlab($clientName, $params, $provider)
    {
        $provider = ('gitlab' == $clientName) ? new Gitlab($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderGoogle($clientName, $params, $provider)
    {
        $provider = ('google' == $clientName) ? new Google($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderlinkedin($clientName, $params, $provider)
    {
        $provider = ('linkedin' == $clientName) ? new LinkedIn($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderSlack($clientName, $params, $provider)
    {
        $provider = ('slack' == $clientName) ? new Slack($params) : $provider;

        return $provider;
    }

    protected function generateStandardProviderTwitch($clientName, $params, $provider)
    {
        $provider = ('twitch' == $clientName) ? new TwitchHelix($params) : $provider;

        return $provider;
    }

    protected function getConfig(string $clientName): array
    {
        if (isset($this->configProvider[$clientName])) {
            return $this->configProvider[$clientName];
        }

        return [];
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

        return $this->generateProvider($clientName, $url, $oauth);
    }
}
