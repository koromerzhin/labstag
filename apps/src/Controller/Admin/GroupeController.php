<?php

namespace Labstag\Controller\Admin;

use Knp\Component\Pager\PaginatorInterface;
use Labstag\Entity\Groupe;
use Labstag\Form\Admin\GroupeType;
use Labstag\Repository\GroupeRepository;
use Labstag\Lib\AdminControllerLib;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Repository\RouteRepository;
use Labstag\RequestHandler\GroupeRequestHandler;

/**
 * @Route("/admin/user/groupe")
 */
class GroupeController extends AdminControllerLib
{

    protected string $headerTitle = "Groupe d'utilisateurs";

    protected string $urlHome = 'admin_groupuser_index';

    /**
     * @Route("/trash", name="admin_groupuser_trash", methods={"GET"})
     * @Route("/", name="admin_groupuser_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function index(GroupeRepository $repository): Response
    {
        return $this->listOrTrash(
            $repository,
            [
                'trash' => 'findTrashForAdmin',
                'all'   => 'findAllForAdmin',
            ],
            'admin/groupe/index.html.twig',
            [
                'new'   => 'admin_groupuser_new',
                'empty' => 'api_action_empty',
                'trash' => 'admin_groupuser_trash',
                'list'  => 'admin_groupuser_index',
            ],
            [
                'list'    => 'admin_groupuser_index',
                'show'    => 'admin_groupuser_show',
                'edit'    => 'admin_groupuser_edit',
                'preview' => 'admin_groupuser_preview',
                'delete'  => 'api_action_delete',
                'guard'   => 'admin_groupuser_guard',
                'destroy' => 'api_action_destroy',
            ]
        );
    }

    /**
     * @Route("/new", name="admin_groupuser_new", methods={"GET","POST"})
     */
    public function new(GroupeRequestHandler $requestHandler): Response
    {
        return $this->create(
            new Groupe(),
            GroupeType::class,
            $requestHandler,
            ['list' => 'admin_groupuser_index']
        );
    }

    /**
     * @Route("/{id}", name="admin_groupuser_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_groupuser_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(Groupe $groupe): Response
    {
        return $this->showOrPreview(
            $groupe,
            'admin/groupe/show.html.twig',
            [
                'delete'  => 'api_action_delete',
                'restore' => 'api_action_restore',
                'destroy' => 'api_action_destroy',
                'edit'    => 'admin_groupuser_edit',
                'guard'   => 'admin_groupuser_guard',
                'list'    => 'admin_groupuser_index',
                'trash'   => 'admin_groupuser_trash',
            ]
        );
    }

    /**
     * @Route("/{id}/guard", name="admin_groupuser_guard")
     */
    public function guard(Groupe $groupe): Response
    {
        $breadcrumb = [
            'Guard' => $this->generateUrl(
                'admin_groupuser_guard',
                [
                    'id' => $groupe->getId(),
                ]
            ),
        ];
        $this->addBreadcrumbs($breadcrumb);
        $this->btnInstance->addBtnList(
            'admin_groupuser_index',
            'Liste',
        );
        $this->btnInstance->addBtnShow(
            'admin_groupuser_show',
            'Show',
            [
                'id' => $groupe->getId(),
            ]
        );

        $this->btnInstance->addBtnEdit(
            'admin_groupuser_edit',
            'Editer',
            [
                'id' => $groupe->getId(),
            ]
        );

        $routes = $this->guardService->getGuardRoutesForGroupe($groupe);
        if (count($routes) == 0) {
            $this->addFlash(
                'danger',
                "Le groupe superadmin n'est pas un groupe qui peut avoir des droits spécifique"
            );
            return $this->redirect($this->generateUrl('admin_groupuser_index'));
        }

        return $this->render(
            'admin/guard/group.html.twig',
            [
                'group'  => $groupe,
                'routes' => $routes,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="admin_groupuser_edit", methods={"GET","POST"})
     */
    public function edit(Groupe $groupe, GroupeRequestHandler $requestHandler): Response
    {
        return $this->update(
            GroupeType::class,
            $groupe,
            $requestHandler,
            [
                'delete' => 'api_action_delete',
                'list'   => 'admin_groupuser_index',
                'guard'  => 'admin_groupuser_guard',
                'show'   => 'admin_groupuser_show',
            ]
        );
    }
}
