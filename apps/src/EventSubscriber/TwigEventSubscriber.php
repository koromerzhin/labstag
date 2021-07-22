<?php

namespace Labstag\EventSubscriber;

use Labstag\Service\DataService;
use Labstag\Singleton\BreadcrumbsSingleton;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    const ADMIN_CONTROLLER   = '/(Controller\\\Admin)/';
    const LABSTAG_CONTROLLER = '/(Labstag)/';

    protected CsrfTokenManagerInterface $csrfTokenManager;

    protected DataService $dataService;

    protected RouterInterface $router;

    protected Security $security;

    protected Environment $twig;

    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(
        RouterInterface $router,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        DataService $dataService,
        Security $security
    )
    {
        $this->security         = $security;
        $this->urlGenerator     = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->router           = $router;
        $this->twig             = $twig;
        $this->dataService      = $dataService;
    }

    public static function getSubscribedEvents()
    {
        return [ControllerEvent::class => 'onControllerEvent'];
    }

    public function onControllerEvent(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $this->setLoginPage($event);
        $this->setAdminPages($event);
        $this->setConfig($event, $request);
    }

    protected function setAdminPages(ControllerEvent $event): void
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        if (0 == substr_count($controller, 'Controller\Admin')) {
            return;
        }

        $this->setBreadCrumbsAdmin();
    }

    protected function setBreadCrumbsAdmin()
    {
        $adminBreadcrumbs = [
            'Home' => $this->router->generate('admin'),
        ];

        BreadcrumbsSingleton::getInstance()->add($adminBreadcrumbs);
    }

    protected function setConfig(ControllerEvent $event, Request $request): void
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        $matches    = [];
        preg_match(self::LABSTAG_CONTROLLER, $controller, $matches);
        if (0 == count($matches)) {
            return;
        }

        $globals        = $this->twig->getGlobals();
        $config         = isset($globals['config']) ? $globals['config'] : $this->dataService->getConfig();
        $config['meta'] = !array_key_exists('meta', $config) ? [] : $config['meta'];
        $this->setMetaTitleGlobal($config);
        preg_match(self::ADMIN_CONTROLLER, $controller, $matches);
        $state = (0 == count($matches));
        $this->setConfigGlobal($state, $config, $request);
        if (!$state) {
            $config['meta']['robots'] = 'noindex';
        }

        ksort($config['meta']);

        $this->twig->addGlobal('config', $config);
    }

    protected function setLoginPage(ControllerEvent $event): void
    {
        $currentRoute = $event->getRequest()->attributes->get('_route');
        $routes       = [
            'app_login',
            'admin_profil',
        ];

        if (!in_array($currentRoute, $routes)) {
            return;
        }

        $this->twig->addGlobal(
            'oauthActivated',
            $this->dataService->getOauthActivated($this->security->getUser())
        );
    }

    private function setConfigGlobal(bool $enable, array &$config, Request $request)
    {
        if (!$enable) {
            return;
        }

        $this->setMetaTitle($config);
        $this->setMetaDescription($config);
        $url                            = $this->urlGenerator->generate(
            $request->attributes->get('_route'),
            $request->attributes->get('_route_params'),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $config['meta']['og:url']       = $url;
        $config['meta']['twitter:url']  = $url;
        $config['meta']['og:type']      = 'website';
        $config['meta']['twitter:card'] = 'summary_large_image';
    }

    private function setMetaDescription(&$config)
    {
        $meta = $config['meta'];
        if (!array_key_exists('description', $meta) || array_key_exists('og:description', $meta) || array_key_exists('twitter:description', $meta)) {
            return;
        }

        $meta['og:description']      = $meta['description'];
        $meta['twitter:description'] = $meta['description'];

        $config['meta'] = $meta;
    }

    private function setMetaTitle(&$config)
    {
        if (!array_key_exists('site_title', $config)) {
            return;
        }

        $meta = $config['meta'];
        if (array_key_exists('og:title', $meta) || array_key_exists('twitter:title', $meta)) {
            return;
        }

        $meta['og:title']      = $config['site_title'];
        $meta['twitter:title'] = $config['site_title'];

        $config['meta'] = $meta;
    }

    private function setMetaTitleGlobal(&$config)
    {
        $meta = $config['meta'];
        if (!array_key_exists('site_title', $config) && array_key_exists('title', $meta)) {
            return;
        }

        $config['meta']['title'] = $config['site_title'];
    }
}
