<?php

namespace Labstag\Service;

use Knp\Component\Pager\PaginatorInterface;
use Labstag\Lib\AdminControllerLib;
use Labstag\Lib\RequestHandlerLib;
use Labstag\Lib\ServiceEntityRepositoryLib;
use Labstag\Service\AdminBoutonService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class AdminCrudService
{

    protected AdminBoutonService $adminBoutonService;

    protected PaginatorInterface $paginator;

    protected RequestStack $requestStack;

    protected Request $request;

    protected FormFactoryInterface $formFactory;

    protected RouterInterface $router;

    protected SessionInterface $session;

    protected Environment $twig;

    protected AdminControllerLib $controller;

    protected string $headerTitle = '';

    protected string $urlHome = '';

    public function __construct(
        Environment $twig,
        AdminBoutonService $adminBoutonService,
        PaginatorInterface $paginator,
        RequestStack $requestStack,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        SessionInterface $session
    )
    {
        $this->twig         = $twig;
        $this->session      = $session;
        $this->router       = $router;
        $this->formFactory  = $formFactory;
        $this->requestStack = $requestStack;
        /** @var Request $request */
        $request                  = $this->requestStack->getCurrentRequest();
        $this->request            = $request;
        $this->paginator          = $paginator;
        $this->adminBoutonService = $adminBoutonService;
    }

    public function addBreadcrumbs(array $breadcrumbs): void
    {
        BreadcrumbsService::getInstance()->add($breadcrumbs);
    }

    public function setPage($header, $url)
    {
        $this->headerTitle = $header;
        $this->urlHome     = $url;
    }

    protected function setBtnList(array $url): void
    {
        if (!isset($url['list'])) {
            return;
        }

        $this->adminBoutonService->addBtnList(
            $url['list'],
            'Liste',
        );
    }

    protected function setBtnViewUpdate(array $url, object $entity): void
    {
        $this->setBtnList($url);
        $this->setBtnShow($url, $entity);
        $this->setBtnGuard($url, $entity);
        $this->setBtnDelete($url, $entity);
    }

    protected function setBtnShow(array $url, object $entity): void
    {
        if (!isset($url['show'])) {
            return;
        }

        $this->adminBoutonService->addBtnShow(
            $url['show'],
            'Show',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function setBtnGuard(array $url, object $entity): void
    {
        if (!isset($url['guard'])) {
            return;
        }

        $this->adminBoutonService->addBtnGuard(
            $url['guard'],
            'Guard',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function setBtnDelete(array $url, object $entity): void
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

        $this->adminBoutonService->addBtnDelete(
            $entity,
            $urlsDelete,
            'Supprimer',
            [
                'id'     => $entity->getId(),
                'entity' => $this->classEntity($entity),
            ]
        );
    }

    protected function classEntity($entity)
    {
        $class = get_class($entity);

        $class = str_replace('Labstag\\Entity\\', '', $class);

        return strtolower($class);
    }

    public function setController(AdminControllerLib $controller): void
    {
        $this->controller = $controller;
    }

    protected function listOrTrashRouteTrash($url, $actions)
    {
        $breadcrumb = [
            'Trash' => $this->router->generate(
                'admin_adresseuser_trash'
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        if (isset($url['list'])) {
            $this->adminBoutonService->addBtnList(
                $url['list']
            );
        }

        if (isset($url['empty'])) {
            $this->adminBoutonService->addBtnEmpty(
                [
                    'empty' => $url['empty'],
                    'list'  => $url['list'],
                ]
            );
        }

        if (isset($actions['destroy'])) {
            $this->twig->addGlobal(
                'modalDestroy',
                true
            );
        }

        if (isset($actions['restore'])) {
            $this->twig->addGlobal(
                'modalRestore',
                true
            );
        }
    }

    public function listOrTrash(
        ServiceEntityRepositoryLib $repository,
        array $methods,
        string $html,
        array $url = [],
        array $actions = []
    ): Response
    {
        $routeCurrent = $this->request->get('_route');
        $routeType    = (0 != substr_count($routeCurrent, 'trash')) ? 'trash' : 'all';
        $method       = $methods[$routeType];

        if ('trash' == $routeType) {
            $this->listOrTrashRouteTrash($url, $actions);
        } elseif (isset($url['trash'])) {
            $this->adminBoutonService->addBtnTrash(
                $url['trash']
            );
            if (isset($actions['delete'])) {
                $this->twig->addGlobal(
                    'modalDelete',
                    true
                );
            }

            if (isset($actions['workflow'])) {
                $this->twig->addGlobal(
                    'modalWorkflow',
                    true
                );
            }
        }

        if (isset($url['new']) && 'trash' != $routeType) {
            $this->adminBoutonService->addBtnNew(
                $url['new']
            );
        }

        $pagination = $this->paginator->paginate(
            $repository->$method(),
            $this->request->query->getInt('page', 1),
            10
        );

        return $this->controller->render(
            $html,
            [
                'pagination' => $pagination,
                'actions'    => $actions,
            ]
        );
    }

    protected function showOrPreviewaddBreadcrumbs($url, $routeType, $routeCurrent, $entity)
    {
        if ('preview' == $routeType && isset($url['trash'])) {
            $breadcrumb = [
                'Trash' => $this->router->generate(
                    $url['trash']
                ),
            ];
            $this->addBreadcrumbs($breadcrumb);
        }

        $breadcrumb = [
            $routeType => $this->router->generate(
                $routeCurrent,
                [
                    'id' => $entity->getId(),
                ]
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
    }

    protected function showOrPreviewaddBtnList($url, $routeType)
    {
        if (!(isset($url['list']) && 'show' == $routeType)) {
            return;
        }

        $this->adminBoutonService->addBtnList(
            $url['list'],
            'Liste',
        );
    }

    protected function showOrPreviewaddBtnTrash($url, $routeType)
    {
        if (!(isset($url['trash']) && 'preview' == $routeType)) {
            return;
        }

        $this->adminBoutonService->addBtnTrash(
            $url['trash'],
            'Trash',
        );
    }

    protected function showOrPreviewaddBtnGuard($url, $routeType, $entity)
    {
        if (!(isset($url['guard']) && 'show' == $routeType)) {
            return;
        }

        $this->adminBoutonService->addBtnGuard(
            $url['guard'],
            'Guard',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function showOrPreviewaddBtnEdit($url, $routeType, $entity)
    {
        if (!(isset($url['edit']) && 'show' == $routeType)) {
            return;
        }

        $this->adminBoutonService->addBtnEdit(
            $url['edit'],
            'Editer',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    protected function showOrPreviewaddBtnRestore($url, $routeType, $entity)
    {
        dump($routeType);
        if (isset($url['restore']) && 'preview' == $routeType) {
            $this->adminBoutonService->addBtnRestore(
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

    protected function showOrPreviewaddBtnDestroy($url, $routeType, $entity)
    {
        if (!(isset($url['destroy']) && 'preview' == $routeType)) {
            return;
        }

        $this->adminBoutonService->addBtnDestroy(
            $entity,
            [
                'destroy' => $url['destroy'],
                'list'    => $url['trash'],
            ],
            'Destroy',
            [
                'id' => $entity->getId(),
            ]
        );
    }

    public function showOrPreview(
        object $entity,
        string $twigShow,
        array $url = []
    ): Response
    {
        $routeCurrent = $this->request->get('_route');
        $routeType    = (0 != substr_count($routeCurrent, 'preview')) ? 'preview' : 'show';
        $this->showOrPreviewaddBreadcrumbs($url, $routeType, $routeCurrent, $entity);
        $this->showOrPreviewaddBtnList($url, $routeType);
        $this->showOrPreviewaddBtnGuard($url, $routeType, $entity);
        $this->showOrPreviewaddBtnTrash($url, $routeType);
        $this->showOrPreviewaddBtnEdit($url, $routeType, $entity);
        $this->showOrPreviewaddBtnRestore($url, $routeType, $entity);
        $this->showOrPreviewaddBtnDestroy($url, $routeType, $entity);

        if (isset($url['delete']) && 'show' == $routeType) {
            $urlsDelete = [
                'delete' => $url['delete'],
            ];
            if (isset($url['list'])) {
                $urlsDelete['list'] = $url['list'];
            }

            $this->adminBoutonService->addBtnDelete(
                $entity,
                $urlsDelete,
                'Supprimer',
                [
                    'id'     => $entity->getId(),
                    'entity' => $this->classEntity($entity),
                ]
            );
        }

        return $this->controller->render(
            $twigShow,
            ['entity' => $entity]
        );
    }

    public function create(
        object $entity,
        string $formType,
        RequestHandlerLib $handler,
        array $url = []
    ): Response
    {
        $routeCurrent = $this->request->get('_route');
        $breadcrumb   = [
            'New' => $this->router->generate(
                $routeCurrent
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        if (isset($url['list'])) {
            $this->adminBoutonService->addBtnList(
                $url['list']
            );
        }

        $oldEntity = clone $entity;
        $form      = $this->formFactory->create($formType, $entity);
        $this->adminBoutonService->addBtnSave($form->getName(), 'Ajouter');
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($oldEntity, $entity);
            if (isset($url['list'])) {
                return new RedirectResponse(
                    $this->router->generate($url['list'])
                );
            }
        }

        return $this->controller->render(
            'admin/crud/form.html.twig',
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ]
        );
    }

    public function update(
        string $formType,
        object $entity,
        RequestHandlerLib $handler,
        array $url = [],
        string $twig = 'admin/crud/form.html.twig'
    ): Response
    {
        $routeCurrent = $this->request->get('_route');
        $breadcrumb   = [
            'edit' => $this->router->generate(
                $routeCurrent,
                [
                    'id' => $entity->getId(),
                ]
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        $this->setBtnViewUpdate($url, $entity);
        $oldEntity = clone $entity;
        $form      = $this->formFactory->create($formType, $entity);
        $this->adminBoutonService->addBtnSave($form->getName(), 'Sauvegarder');
        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($oldEntity, $entity);
            /** @var Session $session */
            $session = $this->session;
            $session->getFlashBag()->add(
                'success',
                'Données sauvegardé'
            );
            if (isset($url['list'])) {
                return new RedirectResponse(
                    $this->router->generate($url['list'])
                );
            }
        }

        return $this->controller->render(
            $twig,
            [
                'entity' => $entity,
                'form'   => $form->createView(),
            ]
        );
    }
}
