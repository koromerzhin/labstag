<?php

namespace Labstag\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Interfaces\DomainInterface;
use Labstag\Interfaces\EntityInterface;
use Labstag\Lib\RepositoryLib;
use Labstag\Service\GuardService;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

class BtnService
{

    protected array $bouton = [];

    public function __construct(
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RequestStack $requeststack,
        protected EntityManagerInterface $entityManager,
        protected Environment $twigEnvironment,
        protected RouterInterface $router,
        protected TokenStorageInterface $tokenStorage,
        protected CsrfTokenManagerInterface $csrfTokenManager,
        protected GuardService $guardService
    )
    {
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
        EntityInterface $entity,
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

        $methods = get_class_methods($entity);
        if (!$this->isRoutesEnable($routes) || !in_array('getId', $methods)) {
            return $this;
        }

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['delete'] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
        $code  = 'delete'.$entity->getId();
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'id'       => 'DeleteForm',
            'is'       => 'link-btnadmindelete',
            'token'    => $token,
            'redirect' => $this->router->generate($route['list']),
            'url'      => $this->router->generate(
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
        EntityInterface $entity,
        array $route,
        string $text = 'Destroy',
        array $routeParam = []
    ): void
    {
        $this->addBtnDestroyRestore('destroy', $entity, $route, $routeParam, $text);
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

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['empty'] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
        $code  = 'empty';
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'is'       => 'link-btnadminempty',
            'token'    => $token,
            'redirect' => $this->router->generate($route['list']),
            'url'      => $this->router->generate(
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

    public function addBtnImport(string $route, string $text = 'Import'): self
    {
        if ('' == $route || !$this->isRouteEnable($route)) {
            return $this;
        }

        $this->add(
            'btn-admin-header-import',
            $text,
            [
                'href' => $this->router->generate($route),
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
        EntityInterface $entity,
        array $route,
        string $text = 'Restore',
        array $routeParam = []
    ): void
    {
        $this->addBtnDestroyRestore('restore', $entity, $route, $routeParam, $text);
    }

    public function addBtnSave(string $form, string $text = 'Sauvegarder'): self
    {
        $this->add(
            'btn-admin-header-save',
            $text,
            [
                'id'   => 'SaveForm',
                'form' => $form,
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

    public function addNewImport(
        RepositoryLib $serviceEntityRepositoryLib,
        array $methods,
        string $routeType,
        array $url = [],
    ): void
    {
        $this->listOrTrashRouteTrashsetTrashIcon(
            $methods,
            $serviceEntityRepositoryLib,
            $url,
            $routeType
        );

        if (isset($url['new']) && 'trash' != $routeType) {
            $this->addBtnNew(
                $url['new']
            );
        }

        if (isset($url['import']) && 'trash' != $routeType) {
            $this->addBtnImport(
                $url['import']
            );
        }
    }

    public function addRestoreSelection(
        array $routes,
        string $code,
        string $title = 'Restaurer'
    ): void
    {
        $this->addBtnVider('restories', $routes, $code, $title);
    }

    public function addSupprimerSelection(
        array $routes,
        string $code,
        string $title = 'Supprimer'
    ): void
    {
        $this->addBtnVider('deleties', $routes, $code, $title);
    }

    public function addViderSelection(
        array $routes,
        string $code,
        string $title = 'Supprimer'
    ): void
    {
        $this->addBtnVider('empties', $routes, $code, $title);
    }

    /**
     * @return array<mixed, array{icon: string, text: string, attr: mixed[]}>
     */
    public function get(): array
    {
        return $this->bouton;
    }

    public function listOrTrashRouteTrash(
        array $url,
        RepositoryLib $serviceEntityRepositoryLib
    ): void
    {
        $path   = explode('\\', $serviceEntityRepositoryLib->getClassName());
        $entity = strtolower(array_pop($path));
        if (isset($url['list'])) {
            $this->addBtnList(
                $url['list']
            );
        }

        if (isset($url['empty'])) {
            $this->addBtnEmpty(
                [
                    'empty' => $url['empty'],
                    'list'  => $url['list'],
                ],
                $entity
            );
        }

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['destroy'] = (isset($url['destroy']));
        $modal['restore'] = (isset($url['restore']));
        $this->twigEnvironment->addGlobal('modal', $modal);
        /** @var Request $request */
        $request     = $this->requeststack->getCurrentRequest();
        $all         = $request->attributes->all();
        $route       = $all['_route'];
        $routeParams = $all['_route_params'];

        $path   = explode('\\', $serviceEntityRepositoryLib->getClassName());
        $entity = strtolower(array_pop($path));
        $this->addViderSelection(
            [
                'redirect' => [
                    'href'   => $route,
                    'params' => $routeParams,
                ],
                'url'      => [
                    'href'   => 'api_action_destroies',
                    'params' => ['entity' => $entity],
                ],
            ],
            'destroies'
        );

        $path   = explode('\\', $serviceEntityRepositoryLib->getClassName());
        $entity = strtolower(array_pop($path));
        $this->addRestoreSelection(
            [
                'redirect' => [
                    'href'   => $route,
                    'params' => $routeParams,
                ],
                'url'      => [
                    'href'   => 'api_action_restories',
                    'params' => ['entity' => $entity],
                ],
            ],
            'restories'
        );
    }

    public function listOrTrashRouteTrashsetTrashIcon(
        array $methods,
        RepositoryLib $serviceEntityRepositoryLib,
        array $url,
        string $routeType
    ): void
    {
        if ('trash' == $routeType) {
            $this->listOrTrashRouteTrash($url, $serviceEntityRepositoryLib);

            return;
        }

        if (isset($url['trash'])) {
            $this->setTrashIcon($methods, $serviceEntityRepositoryLib, $url);
        }
    }

    public function setBtnDelete(array $url, EntityInterface $entity): void
    {
        if (!isset($url['delete'])) {
            return;
        }

        $urlsDelete = [
            'delete' => $url['delete'],
        ];
        if (isset($url['list'])) {
            $urlsDelete['list'] = $url['list'];
        }

        $this->addBtnDelete(
            $entity,
            $urlsDelete,
            'Supprimer',
            [
                'id'     => $entity->getId(),
                'entity' => $this->classEntity($entity),
            ]
        );
    }

    public function setBtnDeleties(
        string $routeType,
        string $route,
        array $routeParams,
        RepositoryLib $serviceEntityRepositoryLib
    ): void
    {
        if ('trash' == $routeType) {
            return;
        }

        $path   = explode('\\', $serviceEntityRepositoryLib->getClassName());
        $entity = strtolower(array_pop($path));
        $this->addSupprimerSelection(
            [
                'redirect' => [
                    'href'   => $route,
                    'params' => $routeParams,
                ],
                'url'      => [
                    'href'   => 'api_action_deleties',
                    'params' => ['entity' => $entity],
                ],
            ],
            'deleties'
        );
    }

    public function setBtnGuard(array $url, EntityInterface $entity): void
    {
        if (!isset($url['guard']) || !$this->enableBtnGuard($entity)) {
            return;
        }

        $this->addBtnGuard(
            $url['guard'],
            'Guard',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    public function setBtnList(array $url): void
    {
        if (!isset($url['list'])) {
            return;
        }

        $this->addBtnList(
            $url['list'],
            'Liste',
        );
    }

    public function setBtnListOrTrash(
        DomainInterface $domain,
        string $routeType
    ): void
    {
        $url                        = $domain->getUrlAdmin();
        $serviceEntityRepositoryLib = $domain->getRepository();
        /** @var Request $request */
        $request     = $this->requeststack->getCurrentRequest();
        $all         = $request->attributes->all();
        $route       = $all['_route'];
        $routeParams = $all['_route_params'];
        $methods     = $domain->getMethodsList();
        $this->addNewImport($serviceEntityRepositoryLib, $methods, $routeType, $url);
        $this->setBtnDeleties($routeType, $route, $routeParams, $serviceEntityRepositoryLib);
    }

    public function setBtnShow(array $url, EntityInterface $entity): void
    {
        if (!isset($url['show'])) {
            return;
        }

        $this->addBtnShow(
            $url['show'],
            'Show',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    public function setBtnViewUpdate(
        array $url,
        EntityInterface $entity
    ): void
    {
        $this->setBtnList($url);
        if (null === $entity->getId() || '' === $entity->getId()) {
            return;
        }

        $functions = [
            'setBtnShow',
            'setBtnGuard',
            'setBtnDelete',
        ];

        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            call_user_func_array($callable, [$url, $entity]);
        }
    }

    public function setTrashIcon(
        array $methods,
        RepositoryLib $serviceEntityRepositoryLib,
        array $url
    ): void
    {
        $methodTrash      = $methods['trash'];
        $filterCollection = $this->entityManager->getFilters();
        $filterCollection->disable('softdeleteable');
        /** @var callable $callable */
        $callable = [
            $serviceEntityRepositoryLib,
            $methodTrash,
        ];
        $trash = call_user_func($callable, []);
        if (!$trash instanceof QueryBuilder) {
            throw new RuntimeException('trash must be a QueryBuilder');
        }

        $query  = $trash->getQuery();
        $result = $query->getResult();
        $total  = is_countable($result) ? count($result) : 0;
        $filterCollection->enable('softdeleteable');
        if (0 != $total) {
            $this->addBtnTrash(
                $url['trash']
            );
        }

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['delete']   = (isset($url['delete']));
        $modal['workflow'] = (isset($url['workflow']));

        $this->twigEnvironment->addGlobal('modal', $modal);
    }

    public function showOrPreviewadd(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        $functions = [
            'showOrPreviewaddBtnList',
            'showOrPreviewaddBtnGuard',
            'showOrPreviewaddBtnTrash',
            'showOrPreviewaddBtnEdit',
            'showOrPreviewaddBtnRestore',
            'showOrPreviewaddBtnDestroy',
        ];

        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            call_user_func_array($callable, [$url, $routeType, $entity]);
        }
    }

    public function showOrPreviewaddBtnDestroy(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        if (!(isset($url['destroy']) && 'preview' == $routeType)) {
            return;
        }

        $this->addBtnDestroy(
            $entity,
            [
                'destroy' => $url['destroy'],
                'list'    => $url['trash'],
            ],
            'Destroy',
            [
                'id'     => $entity->getId(),
                'entity' => $this->classEntity($entity),
            ]
        );
    }

    public function showOrPreviewaddBtnEdit(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        if (!(isset($url['edit']) && 'show' == $routeType) || !$this->isGranted('edit', $entity)) {
            return;
        }

        $this->addBtnEdit(
            $url['edit'],
            'Editer',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    public function showOrPreviewaddBtnGuard(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        if (!(isset($url['guard']) && 'show' == $routeType) || !$this->enableBtnGuard($entity)) {
            return;
        }

        $this->addBtnGuard(
            $url['guard'],
            'Guard',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    public function showOrPreviewaddBtnList(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        unset($entity);
        if (!(isset($url['list']) && 'show' == $routeType)) {
            return;
        }

        $this->addBtnList(
            $url['list'],
            'Liste',
        );
    }

    public function showOrPreviewaddBtnRestore(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        if (isset($url['restore']) && 'preview' == $routeType) {
            $this->addBtnRestore(
                $entity,
                [
                    'restore' => $url['restore'],
                    'list'    => $url['trash'],
                ],
                'Restore',
                [
                    'id'     => $entity->getId(),
                    'entity' => $this->classEntity($entity),
                ]
            );
        }
    }

    public function showOrPreviewaddBtnTrash(
        array $url,
        string $routeType,
        EntityInterface $entity
    ): void
    {
        unset($entity);
        if (!(isset($url['trash']) && 'preview' == $routeType)) {
            return;
        }

        $this->addBtnTrash(
            $url['trash'],
            'Trash',
        );
    }

    protected function addBtnVider(
        string $codemodal,
        array $routes,
        string $code,
        string $title = 'Restaurer',
    ): void
    {
        $value = $this->csrfTokenManager->getToken($code)->getValue();
        if ($this->arrayKeyExistsRedirect($routes) || $this->arrayKeyExistsUrl($routes)) {
            return;
        }

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal[$codemodal] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
        $this->add(
            'btn-admin-header-'.$codemodal,
            $title,
            [
                'is'       => 'link-btnadmin'.$codemodal,
                'token'    => $value,
                'redirect' => $this->router->generate(
                    $routes['redirect']['href'],
                    $routes['redirect']['params']
                ),
                'url'      => $this->router->generate(
                    $routes['url']['href'],
                    $routes['url']['params']
                ),
            ]
        );
    }

    protected function classEntity(EntityInterface $entity): string
    {
        $path = explode('\\', $entity::class);

        return strtolower(array_pop($path));
    }

    protected function enableBtnGuard(EntityInterface $entity): bool
    {
        if ($entity instanceof User) {
            $routes = $this->guardService->getGuardRoutesForUser($entity);

            return 0 != count($routes);
        }

        if (!$entity instanceof Groupe) {
            return false;
        }

        $routes = $this->guardService->getGuardRoutesForGroupe($entity);

        return 0 != count($routes);
    }

    protected function isGranted(
        string $method,
        EntityInterface $entity
    ): bool
    {
        return $this->authorizationChecker->isGranted($method, $entity);
    }

    protected function isRouteEnable(string $route): bool
    {
        $token = $this->tokenStorage->getToken();

        return $this->guardService->guardRoute($route, $token);
    }

    protected function isRoutesEnable(array $routes): bool
    {
        $return = true;
        foreach ($routes as $route) {
            $state = $this->isRouteEnable($route);
            if ($state) {
                continue;
            }

            $return = false;

            break;
        }

        return $return;
    }

    private function addBtnDestroyRestore(
        string $word,
        EntityInterface $entity,
        array $route,
        array $routeParam,
        string $text
    ): void
    {
        if (!isset($route['list']) || !isset($route[$word])) {
            return;
        }

        $routes = [
            $route[$word],
            $route['list'],
        ];

        if (!$this->isRoutesEnable($routes)) {
            return;
        }

        $globals = $this->twigEnvironment->getGlobals();
        $modal   = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal[$word] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
        $code  = $word.$entity->getId();
        $token = $this->csrfTokenManager->getToken($code)->getValue();
        $attr  = [
            'token'    => $token,
            'is'       => 'link-btnadmin'.$word,
            'redirect' => $this->router->generate($route['list']),
            'url'      => $this->router->generate(
                $route[$word],
                $routeParam
            ),
        ];

        $this->add(
            'btn-admin-header-'.$word,
            $text,
            $attr
        );
    }

    private function arrayKeyExistsRedirect(array $routes): bool
    {
        return !array_key_exists('redirect', $routes)
        || !array_key_exists('href', $routes['redirect'])
        || !array_key_exists('params', $routes['redirect'])
        || !$this->isRouteEnable($routes['redirect']['href']);
    }

    private function arrayKeyExistsUrl(array $routes): bool
    {
        return !array_key_exists('url', $routes)
        || !array_key_exists('href', $routes['url'])
        || !array_key_exists('params', $routes['url'])
        || !$this->isRouteEnable($routes['url']['href']);
    }
}
