<?php

namespace Labstag\Controller\Admin\User;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\LienUser;
use Labstag\Form\Admin\User\LienUserType;
use Labstag\Lib\AdminControllerLib;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Repository\AttachmentRepository;
use Labstag\Repository\LienUserRepository;
use Labstag\RequestHandler\AttachmentRequestHandler;
use Labstag\RequestHandler\LienUserRequestHandler;
use Labstag\Service\GuardService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user/lien")
 */
class LienUserController extends AdminControllerLib
{

    protected string $headerTitle = 'Lien utilisateurs';

    protected string $urlHome = 'admin_lienuser_index';

    /**
     * @Route("/{id}/edit", name="admin_lienuser_edit", methods={"GET","POST"})
     */
    public function edit(
        UploadAnnotationReader $uploadAnnotReader,
        GuardService $guarService,
        AttachmentRepository $attachmentRepository,
        AttachmentRequestHandler $attachmentRH,
        LienUser $lienUser,
        LienUserRequestHandler $requestHandler
    ): Response
    {
        return $this->update(
            $uploadAnnotReader,
            $guarService,
            $attachmentRepository,
            $attachmentRH,
            $requestHandler,
            LienUserType::class,
            $lienUser,
            [
                'delete' => 'api_action_delete',
                'list'   => 'admin_lienuser_index',
                'show'   => 'admin_lienuser_show',
            ]
        );
    }

    /**
     * @Route("/trash",  name="admin_lienuser_trash", methods={"GET"})
     * @Route("/",       name="admin_lienuser_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function indexOrTrash(LienUserRepository $lienUserRepository): Response
    {
        return $this->listOrTrash(
            $lienUserRepository,
            [
                'trash' => 'findTrashForAdmin',
                'all'   => 'findAllForAdmin',
            ],
            'admin/user/lien_user/index.html.twig',
            [
                'new'   => 'admin_lienuser_new',
                'empty' => 'api_action_empty',
                'trash' => 'admin_lienuser_trash',
                'list'  => 'admin_lienuser_index',
            ],
            [
                'list'    => 'admin_lienuser_index',
                'show'    => 'admin_lienuser_show',
                'preview' => 'admin_lienuser_preview',
                'edit'    => 'admin_lienuser_edit',
                'delete'  => 'api_action_delete',
                'destroy' => 'api_action_destroy',
                'restore' => 'api_action_restore',
            ]
        );
    }

    /**
     * @Route("/new", name="admin_lienuser_new", methods={"GET","POST"})
     */
    public function new(
        UploadAnnotationReader $uploadAnnotReader,
        AttachmentRepository $attachmentRepository,
        AttachmentRequestHandler $attachmentRH,
        LienUserRequestHandler $requestHandler
    ): Response
    {
        return $this->create(
            $uploadAnnotReader,
            $attachmentRepository,
            $attachmentRH,
            $requestHandler,
            new LienUser(),
            LienUserType::class,
            ['list' => 'admin_lienuser_index']
        );
    }

    /**
     * @Route("/{id}",         name="admin_lienuser_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_lienuser_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(
        LienUser $lienUser
    ): Response
    {
        return $this->renderShowOrPreview(
            $lienUser,
            'admin/user/lien_user/show.html.twig',
            [
                'delete'  => 'api_action_delete',
                'restore' => 'api_action_restore',
                'destroy' => 'api_action_destroy',
                'list'    => 'admin_lienuser_index',
                'edit'    => 'admin_lienuser_edit',
                'trash'   => 'admin_lienuser_trash',
            ]
        );
    }
}
