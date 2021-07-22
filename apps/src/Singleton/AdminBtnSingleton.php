<?php

namespace Labstag\Singleton;

use Labstag\Service\GuardService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class AdminBtnSingleton
{

    protected $bouton;

    protected CsrfTokenManagerInterface $csrfTokenManager;

    protected GuardService $guardService;

    protected bool $init = false;

    protected static $instance = null;

    protected RouterInterface $router;

    protected TokenStorageInterface $token;

    protected Environment $twig;

    protected function __construct()
    {
        $this->bouton = [];
    }

    public function add(
        string $icon,
        string $text,
        array $attr = []
    ): self
    {
        if (!isset($attr['href'])) {
            $attr['href'] = '#';
        }

        $attr = array_merge(
            $attr,
            ['title' => $text]
        );

        $this->bouton[] = [
            'icon' => $icon,
            'text' => $text,
            'attr' => $attr,
        ];

        return $this;
    }

    public function addBtnDelete(
        object $entity,
        array $route,
        string $text = 'Supprimer',
        array $routeParam = []
    ): self
    {
        if (!isset($route['list']) || !isset($route['delete'])) {
            return $this;
        }

        $routes = [
            $route['list'],
            $route['delete'],
        ];

        if (!$this->isRoutesEnable($routes)) {
            return $this;
        }

        $globals         = $this->twig->getGlobals();
        $modal           = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['delete'] = true;
        $this->twig->addGlobal('modal', $modal);
        $code  = 'delete'.$entity->getId();
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'id'            => 'DeleteForm',
            'is'            => 'link-btnadmindelete',
            'data-token'    => $token,
            'data-toggle'   => 'modal',
            'data-target'   => '#delete-modal',
            'data-redirect' => $this->router->generate($route['list']),
            'data-url'      => $this->router->generate(
                $route['delete'],
                $routeParam
            ),
        ];

        $this->add(
            'btn-admin-header-delete',
            $text,
            $attr
        );

        return $this;
    }

    public function addBtnDestroy(
        object $entity,
        array $route,
        string $text = 'Destroy',
        array $routeParam = []
    ): self
    {
        if (!isset($route['list']) || !isset($route['destroy'])) {
            return $this;
        }

        $routes = [
            $route['destroy'],
            $route['list'],
        ];
        if (!$this->isRoutesEnable($routes)) {
            return $this;
        }

        $globals          = $this->twig->getGlobals();
        $modal            = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['destroy'] = true;
        $this->twig->addGlobal('modal', $modal);
        $code  = 'destroy'.$entity->getId();
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'data-toggle'   => 'modal',
            'data-token'    => $token,
            'data-target'   => '#destroy-modal',
            'is'            => 'link-btnadmindestroy',
            'data-redirect' => $this->router->generate($route['list']),
            'data-url'      => $this->router->generate(
                $route['destroy'],
                $routeParam
            ),
        ];

        $this->add(
            'btn-admin-header-destroy',
            $text,
            $attr
        );

        return $this;
    }

    public function addBtnEdit(
        string $route,
        string $text = 'Editer',
        array $routeParam = []
    ): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-edit',
            $text,
            [
                'href' => $this->router->generate($route, $routeParam),
            ]
        );

        return $this;
    }

    public function addBtnEmpty(array $route, string $entity, string $text = 'Vider'): self
    {
        if (!isset($route['list']) || !isset($route['empty'])) {
            return $this;
        }

        $routes = [
            $route['list'],
            $route['empty'],
        ];
        if (!$this->isRoutesEnable($routes)) {
            return $this;
        }

        $globals        = $this->twig->getGlobals();
        $modal          = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['empty'] = true;
        $this->twig->addGlobal('modal', $modal);
        $code  = 'empty';
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'is'            => 'link-btnadminempty',
            'data-toggle'   => 'modal',
            'data-token'    => $token,
            'data-target'   => '#empty-modal',
            'data-redirect' => $this->router->generate($route['list']),
            'data-url'      => $this->router->generate(
                $route['empty'],
                ['entity' => $entity]
            ),
        ];

        $this->add(
            'btn-admin-header-empty',
            $text,
            $attr
        );

        return $this;
    }

    public function addBtnGuard(
        string $route,
        string $text = 'Editer',
        array $routeParam = []
    ): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-guard',
            $text,
            [
                'href' => $this->router->generate($route, $routeParam),
            ]
        );

        return $this;
    }

    public function addBtnList(string $route, string $text = 'Liste'): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-list',
            $text,
            [
                'href' => $this->router->generate($route),
            ]
        );

        return $this;
    }

    public function addBtnNew(string $route, string $text = 'Nouveau'): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-new',
            $text,
            [
                'href' => $this->router->generate($route),
            ]
        );

        return $this;
    }

    public function addBtnRestore(
        object $entity,
        array $route,
        string $text = 'Restore',
        array $routeParam = []
    ): self
    {
        if (!isset($route['restore']) || !isset($route['list'])) {
            return $this;
        }

        $routes = [
            $route['restore'],
            $route['list'],
        ];
        if (!$this->isRoutesEnable($routes)) {
            return $this;
        }

        $globals          = $this->twig->getGlobals();
        $modal            = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['restore'] = true;
        $this->twig->addGlobal('modal', $modal);
        $code  = 'restore'.$entity->getId();
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'data-toggle'   => 'modal',
            'data-token'    => $token,
            'data-target'   => '#restore-modal',
            'is'            => 'link-btnadminrestore',
            'data-redirect' => $this->router->generate(
                $route['list'],
                [
                    'entity' => $this->classEntity($entity),
                ]
            ),
            'data-url'      => $this->router->generate(
                $route['restore'],
                $routeParam
            ),
        ];

        $this->add(
            'btn-admin-header-restore',
            $text,
            $attr
        );

        return $this;
    }

    public function addBtnSave(string $form, string $text = 'Sauvegarder'): self
    {
        $this->add(
            'btn-admin-header-save',
            $text,
            [
                'id'        => 'SaveForm',
                'data-form' => $form,
            ]
        );

        return $this;
    }

    public function addBtnShow(
        string $route,
        string $text = 'Show',
        array $routeParam = []
    ): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-show',
            $text,
            [
                'href' => $this->router->generate($route, $routeParam),
            ]
        );

        return $this;
    }

    public function addBtnTrash(string $route, string $text = 'Corbeille'): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-trash',
            $text,
            [
                'href' => $this->router->generate($route),
            ]
        );

        return $this;
    }

    public function addRestoreSelection(
        array $routes,
        string $code,
        string $title = 'Restaurer'
    ): self
    {
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        if ($this->arrayKeyExistsRedirect($routes) || $this->arrayKeyExistsUrl($routes)) {
            return $this;
        }

        $globals            = $this->twig->getGlobals();
        $modal              = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['restories'] = true;
        $this->twig->addGlobal('modal', $modal);
        $this->add(
            'btn-admin-header-restories',
            $title,
            [
                'is'            => 'link-btnadminrestories',
                'data-toggle'   => 'modal',
                'data-token'    => $token,
                'data-target'   => '#restories-modal',
                'data-redirect' => $this->router->generate($routes['redirect']['href'], $routes['redirect']['params']),
                'data-url'      => $this->router->generate($routes['url']['href'], $routes['url']['params']),
            ]
        );

        return $this;
    }

    public function addSupprimerSelection(
        array $routes,
        string $code,
        string $title = 'Supprimer'
    ): self
    {
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        if ($this->arrayKeyExistsRedirect($routes) || $this->arrayKeyExistsUrl($routes)) {
            return $this;
        }

        $globals           = $this->twig->getGlobals();
        $modal             = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['deleties'] = true;
        $this->twig->addGlobal('modal', $modal);
        $this->setBtnAdd(
            'btn-admin-header-deleties',
            'link-btnadmindeleties',
            'deleties-modal',
            $title,
            $token,
            $routes
        );

        return $this;
    }

    public function addViderSelection(
        array $routes,
        string $code,
        string $title = 'Supprimer'
    ): self
    {
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        if ($this->arrayKeyExistsRedirect($routes) || $this->arrayKeyExistsUrl($routes)) {
            return $this;
        }

        $globals          = $this->twig->getGlobals();
        $modal            = isset($globals['modal']) ? $globals['modal'] : [];
        $modal['empties'] = true;
        $this->twig->addGlobal('modal', $modal);
        $this->setBtnAdd(
            'btn-admin-header-empties',
            'link-btnadminempties',
            'empties-modal',
            $title,
            $token,
            $routes
        );

        return $this;
    }

    public function get(): array
    {
        return $this->bouton;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new AdminBtnSingleton();
        }

        return self::$instance;
    }

    public function isInit()
    {
        return $this->init;
    }

    public function setConf(
        Environment $twig,
        RouterInterface $router,
        TokenStorageInterface $token,
        CsrfTokenManagerInterface $csrfTokenManager,
        GuardService $guardService
    )
    {
        $this->twig             = $twig;
        $this->router           = $router;
        $this->token            = $token;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->guardService     = $guardService;
        $this->init             = true;
    }

    protected function classEntity($entity)
    {
        $class = get_class($entity);

        $class = str_replace('Labstag\\Entity\\', '', $class);

        return strtolower($class);
    }

    protected function isRouteEnable(string $route)
    {
        $token = $this->token->getToken();

        return $this->guardService->guardRoute($route, $token);
    }

    protected function isRoutesEnable(array $routes): bool
    {
        foreach ($routes as $route) {
            $state = $this->isRouteEnable($route);
            if (!$state) {
                return false;
            }
        }

        return true;
    }

    private function arrayKeyExistsRedirect($routes)
    {
        return !array_key_exists('redirect', $routes)
        || !array_key_exists('href', $routes['redirect'])
        || !array_key_exists('params', $routes['redirect'])
        || !$this->isRouteEnable($routes['redirect']['href']);
    }

    private function arrayKeyExistsUrl($routes)
    {
        return !array_key_exists('url', $routes)
        || !array_key_exists('href', $routes['url'])
        || !array_key_exists('params', $routes['url'])
        || !$this->isRouteEnable($routes['url']['href']);
    }

    private function setBtnAdd(
        $name,
        $isinput,
        $target,
        $title,
        $token,
        $routes
    )
    {
        $this->add(
            $name,
            $title,
            [
                'is'            => $isinput,
                'data-toggle'   => 'modal',
                'data-target'   => '#'.$target,
                'data-token'    => $token,
                'data-redirect' => $this->router->generate($routes['redirect']['href'], $routes['redirect']['params']),
                'data-url'      => $this->router->generate($routes['url']['href'], $routes['url']['params']),
            ]
        );
    }
}
