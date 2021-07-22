<?php

namespace Labstag\Controller\Admin\User;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\AdresseUser;
use Labstag\Form\Admin\User\AdresseUserType;
use Labstag\Lib\AdminControllerLib;
use Labstag\Repository\AdresseUserRepository;
use Labstag\RequestHandler\AdresseUserRequestHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user/adresse")
 */
class AdresseUserController extends AdminControllerLib
{

    protected string $headerTitle = 'Adresse utilisateurs';

    protected string $urlHome = 'admin_adresseuser_index';

    /**
     * @Route(
     *  "/{id}/edit",
     *  name="admin_adresseuser_edit",
     *  methods={"GET","POST"}
     * )
     */
    public function edit(AdresseUser $adresseUser, AdresseUserRequestHandler $requestHandler): Response
    {
        return $this->update(
            AdresseUserType::class,
            $adresseUser,
            $requestHandler,
            [
                'delete' => 'api_action_delete',
                'list'   => 'admin_adresseuser_index',
                'show'   => 'admin_adresseuser_show',
            ]
        );
    }

    /**
     * @Route("/trash", name="admin_adresseuser_trash", methods={"GET"})
     * @Route("/", name="admin_adresseuser_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function indexOrTrash(AdresseUserRepository $repository): Response
    {
        return $this->listOrTrash(
            $repository,
            [
                'trash' => 'findTrashForAdmin',
                'all'   => 'findAllForAdmin',
            ],
            'admin/user/adresse_user/index.html.twig',
            [
                'new'   => 'admin_adresseuser_new',
                'empty' => 'api_action_empty',
                'trash' => 'admin_adresseuser_trash',
                'list'  => 'admin_adresseuser_index',
            ],
            [
                'list'    => 'admin_adresseuser_index',
                'show'    => 'admin_adresseuser_show',
                'preview' => 'admin_adresseuser_preview',
                'edit'    => 'admin_adresseuser_edit',
                'delete'  => 'api_action_delete',
                'destroy' => 'api_action_destroy',
                'restore' => 'api_action_restore',
            ]
        );
    }

    /**
     * @Route("/new", name="admin_adresseuser_new", methods={"GET","POST"})
     */
    public function new(AdresseUserRequestHandler $requestHandler): Response
    {
        return $this->create(
            new AdresseUser(),
            AdresseUserType::class,
            $requestHandler,
            ['list' => 'admin_adresseuser_index']
        );
    }

    /**
     * @Route("/{id}", name="admin_adresseuser_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_adresseuser_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(AdresseUser $adresseUser): Response
    {
        return $this->renderShowOrPreview(
            $adresseUser,
            'admin/user/adresse_user/show.html.twig',
            [
                'delete'  => 'api_action_delete',
                'restore' => 'api_action_restore',
                'destroy' => 'api_action_destroy',
                'edit'    => 'admin_adresseuser_edit',
                'list'    => 'admin_adresseuser_index',
                'trash'   => 'admin_adresseuser_trash',
            ]
        );
    }
}
