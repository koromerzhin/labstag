<?php

namespace Labstag\Controller\Admin;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\NoteInterne;
use Labstag\Form\Admin\NoteInterneType;
use Labstag\Lib\AdminControllerLib;
use Labstag\Repository\NoteInterneRepository;
use Labstag\RequestHandler\NoteInterneRequestHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/noteinterne")
 */
class NoteInterneController extends AdminControllerLib
{

    protected string $headerTitle = 'Note interne';

    protected string $urlHome = 'admin_noteinterne_index';

    /**
     * @Route(
     *  "/{id}/edit",
     *  name="admin_noteinterne_edit",
     *  methods={"GET","POST"}
     * )
     */
    public function edit(NoteInterne $noteInterne, NoteInterneRequestHandler $requestHandler): Response
    {
        $this->modalAttachmentDelete();

        return $this->update(
            NoteInterneType::class,
            $noteInterne,
            $requestHandler,
            [
                'delete' => 'api_action_delete',
                'list'   => 'admin_noteinterne_index',
                'show'   => 'admin_noteinterne_show',
            ],
            'admin/note_interne/form.html.twig'
        );
    }

    /**
     * @Route("/trash", name="admin_noteinterne_trash", methods={"GET"})
     * @Route("/", name="admin_noteinterne_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function indexOrTrash(NoteInterneRepository $repository): Response
    {
        return $this->listOrTrash(
            $repository,
            [
                'trash' => 'findTrashForAdmin',
                'all'   => 'findAllForAdmin',
            ],
            'admin/note_interne/index.html.twig',
            [
                'new'   => 'admin_noteinterne_new',
                'empty' => 'api_action_empty',
                'trash' => 'admin_noteinterne_trash',
                'list'  => 'admin_noteinterne_index',
            ],
            [
                'list'     => 'admin_noteinterne_index',
                'show'     => 'admin_noteinterne_show',
                'preview'  => 'admin_noteinterne_preview',
                'edit'     => 'admin_noteinterne_edit',
                'delete'   => 'api_action_delete',
                'destroy'  => 'api_action_destroy',
                'restore'  => 'api_action_restore',
                'workflow' => 'api_action_workflow',
            ]
        );
    }

    /**
     * @Route("/new", name="admin_noteinterne_new", methods={"GET","POST"})
     */
    public function new(NoteInterneRequestHandler $requestHandler): Response
    {
        return $this->create(
            new NoteInterne(),
            NoteInterneType::class,
            $requestHandler,
            ['list' => 'admin_noteinterne_index'],
            'admin/note_interne/form.html.twig'
        );
    }

    /**
     * @Route("/{id}", name="admin_noteinterne_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_noteinterne_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(NoteInterne $noteInterne): Response
    {
        return $this->renderShowOrPreview(
            $noteInterne,
            'admin/note_interne/show.html.twig',
            [
                'delete'  => 'api_action_delete',
                'restore' => 'api_action_restore',
                'destroy' => 'api_action_destroy',
                'list'    => 'admin_noteinterne_index',
                'edit'    => 'admin_noteinterne_edit',
                'trash'   => 'admin_noteinterne_trash',
            ]
        );
    }
}
