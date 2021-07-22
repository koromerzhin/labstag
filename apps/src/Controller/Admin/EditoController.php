<?php

namespace Labstag\Controller\Admin;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Edito;
use Labstag\Form\Admin\EditoType;
use Labstag\Lib\AdminControllerLib;
use Labstag\Repository\EditoRepository;
use Labstag\RequestHandler\EditoRequestHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/edito")
 */
class EditoController extends AdminControllerLib
{

    protected string $headerTitle = 'Edito';

    protected string $urlHome = 'admin_edito_index';

    /**
     * @Route("/{id}/edit", name="admin_edito_edit", methods={"GET","POST"})
     */
    public function edit(Edito $edito, EditoRequestHandler $requestHandler): Response
    {
        $this->modalAttachmentDelete();

        return $this->update(
            EditoType::class,
            $edito,
            $requestHandler,
            [
                'delete' => 'api_action_delete',
                'list'   => 'admin_edito_index',
                'show'   => 'admin_edito_show',
            ],
            'admin/edito/form.html.twig'
        );
    }

    /**
     * @Route("/trash", name="admin_edito_trash", methods={"GET"})
     * @Route("/", name="admin_edito_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function indexOrTrash(EditoRepository $repository): Response
    {
        return $this->listOrTrash(
            $repository,
            [
                'trash' => 'findTrashForAdmin',
                'all'   => 'findAllForAdmin',
            ],
            'admin/edito/index.html.twig',
            [
                'new'   => 'admin_edito_new',
                'empty' => 'api_action_empty',
                'trash' => 'admin_edito_trash',
                'list'  => 'admin_edito_index',
            ],
            [
                'list'     => 'admin_edito_index',
                'show'     => 'admin_edito_show',
                'preview'  => 'admin_edito_preview',
                'edit'     => 'admin_edito_edit',
                'delete'   => 'api_action_delete',
                'destroy'  => 'api_action_destroy',
                'restore'  => 'api_action_restore',
                'workflow' => 'api_action_workflow',
            ]
        );
    }

    /**
     * @Route("/new", name="admin_edito_new", methods={"GET","POST"})
     */
    public function new(EditoRequestHandler $requestHandler): Response
    {
        return $this->create(
            new Edito(),
            EditoType::class,
            $requestHandler,
            ['list' => 'admin_edito_index'],
            'admin/edito/form.html.twig'
        );
    }

    /**
     * @Route("/{id}", name="admin_edito_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_edito_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(Edito $edito): Response
    {
        return $this->renderShowOrPreview(
            $edito,
            'admin/edito/show.html.twig',
            [
                'delete'  => 'api_action_delete',
                'restore' => 'api_action_restore',
                'destroy' => 'api_action_destroy',
                'edit'    => 'admin_edito_edit',
                'list'    => 'admin_edito_index',
                'trash'   => 'admin_edito_trash',
            ]
        );
    }
}
