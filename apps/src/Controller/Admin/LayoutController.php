<?php

namespace Labstag\Controller\Admin;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Block\Custom;
use Labstag\Entity\Layout;
use Labstag\Form\Admin\LayoutType;
use Labstag\Form\Admin\NewLayoutType;
use Labstag\Form\Admin\Search\LayoutType as SearchLayoutType;
use Labstag\Lib\AdminControllerLib;
use Labstag\Repository\Block\CustomRepository;
use Labstag\Repository\LayoutRepository;
use Labstag\RequestHandler\LayoutRequestHandler;
use Labstag\Search\LayoutSearch;
use Labstag\Service\AttachFormService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

#[Route(path: '/admin/layout')]
class LayoutController extends AdminControllerLib
{
    #[Route(path: '/{id}/edit', name: 'admin_layout_edit', methods: ['GET', 'POST'])]
    public function edit(AttachFormService $attachFormService, ?Layout $layout, LayoutRequestHandler $layoutRequestHandler): Response
    {
        $this->modalAttachmentDelete();

        return $this->form(
            $attachFormService,
            $layoutRequestHandler,
            LayoutType::class,
            is_null($layout) ? new Layout() : $layout,
            'admin/layout/form.html.twig'
        );
    }

    /**
     * @IgnoreSoftDelete
     */
    #[Route(path: '/trash', name: 'admin_layout_trash', methods: ['GET'])]
    #[Route(path: '/', name: 'admin_layout_index', methods: ['GET'])]
    public function indexOrTrash(): Response
    {
        $this->btnInstance()->add(
            'btn-admin-header-new',
            'Nouveau',
            [
                'is'       => 'link-btnadminnewblock',
                'data-url' => $this->routerInterface->generate('admin_layout_new'),
            ]
        );
        $form = $this->createForm(
            NewLayoutType::class,
            new Layout(),
            [
                'action' => $this->routerInterface->generate('admin_layout_new'),
            ]
        );

        $repository = $this->getRepository(Layout::class);
        $url        = $this->getUrlAdmin();
        $request    = $this->requeststack->getCurrentRequest();
        $all        = $request->attributes->all();
        $route      = $all['_route'];
        $routeType  = (0 != substr_count((string) $route, 'trash')) ? 'trash' : 'all';
        $this->setBtnListOrTrash($repository, $routeType);
        $pagination = $this->setPagination($repository, $routeType);

        if ('trash' == $routeType && 0 == $pagination->count()) {
            throw new AccessDeniedException();
        }

        $parameters = [
            'newform'    => $form,
            'pagination' => $pagination,
            'actions'    => $url,
        ];
        $parameters = $this->setSearchForms($parameters);

        return $this->renderForm(
            'admin/layout/index.html.twig',
            $parameters
        );
    }

    #[Route(path: '/new', name: 'admin_layout_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        LayoutRepository $layoutRepository,
        LayoutRequestHandler $layoutRequestHandler,
        CustomRepository $customRepository
    ): RedirectResponse
    {
        $post   = $request->request->all('new_layout');
        $custom = $customRepository->findOneBy(
            [
                'id' => $post['custom'],
            ]
        );
        if (!$custom instanceof Custom) {
            return $this->redirectToRoute('admin_layout_index');
        }

        $layout = new Layout();
        $layout->setCustom($custom);
        $layout->setName(Uuid::v1());

        $old = clone $layout;
        $layoutRepository->add($layout);
        $layoutRequestHandler->handle($old, $layout);

        return $this->redirectToRoute('admin_layout_edit', ['id' => $layout->getId()]);
    }

    /**
     * @IgnoreSoftDelete
     */
    #[Route(path: '/{id}', name: 'admin_layout_show', methods: ['GET'])]
    #[Route(path: '/preview/{id}', name: 'admin_layout_preview', methods: ['GET'])]
    public function showOrPreview(Layout $layout): Response
    {
        return $this->renderShowOrPreview(
            $layout,
            'admin/layout/show.html.twig'
        );
    }

    protected function getUrlAdmin(): array
    {
        return [
            'delete'   => 'api_action_delete',
            'destroy'  => 'api_action_destroy',
            'edit'     => 'admin_layout_edit',
            'empty'    => 'api_action_empty',
            'list'     => 'admin_layout_index',
            'preview'  => 'admin_layout_preview',
            'restore'  => 'api_action_restore',
            'show'     => 'admin_layout_show',
            'trash'    => 'admin_layout_trash',
            'workflow' => 'api_action_workflow',
        ];
    }

    /**
     * @return array<string, \LayoutSearch>|array<string, class-string<\Labstag\Form\Admin\Search\LayoutType>>
     */
    protected function searchForm(): array
    {
        return [
            'form' => SearchLayoutType::class,
            'data' => new LayoutSearch(),
        ];
    }

    /**
     * @return mixed[]
     */
    protected function setBreadcrumbsData(): array
    {
        return array_merge(
            parent::setBreadcrumbsData(),
            [
                [
                    'title' => $this->translator->trans('layout.title', [], 'admin.breadcrumb'),
                    'route' => 'admin_layout_index',
                ],
                [
                    'title' => $this->translator->trans('layout.edit', [], 'admin.breadcrumb'),
                    'route' => 'admin_layout_edit',
                ],
                [
                    'title' => $this->translator->trans('layout.new', [], 'admin.breadcrumb'),
                    'route' => 'admin_layout_new',
                ],
                [
                    'title' => $this->translator->trans('layout.trash', [], 'admin.breadcrumb'),
                    'route' => 'admin_layout_trash',
                ],
                [
                    'title' => $this->translator->trans('layout.preview', [], 'admin.breadcrumb'),
                    'route' => 'admin_layout_preview',
                ],
                [
                    'title' => $this->translator->trans('layout.show', [], 'admin.breadcrumb'),
                    'route' => 'admin_layout_show',
                ],
            ]
        );
    }

    /**
     * @return mixed[]
     */
    protected function setHeaderTitle(): array
    {
        $headers = parent::setHeaderTitle();

        return [
            ...$headers, ...
            [
                'admin_bookmark' => $this->translator->trans('layout.title', [], 'admin.header'),
            ],
        ];
    }
}
