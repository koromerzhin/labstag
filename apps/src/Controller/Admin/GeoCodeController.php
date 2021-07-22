<?php

namespace Labstag\Controller\Admin;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\GeoCode;
use Labstag\Form\Admin\GeoCodeType;
use Labstag\Lib\AdminControllerLib;
use Labstag\Repository\GeoCodeRepository;
use Labstag\RequestHandler\GeoCodeRequestHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/geocode")
 */
class GeoCodeController extends AdminControllerLib
{

    protected string $headerTitle = 'Gecode';

    protected string $urlHome = 'admin_geocode_index';

    /**
     * @Route("/{id}/edit", name="admin_geocode_edit", methods={"GET","POST"})
     */
    public function edit(GeoCode $geoCode, GeoCodeRequestHandler $requestHandler): Response
    {
        return $this->update(
            GeoCodeType::class,
            $geoCode,
            $requestHandler,
            [
                'delete' => 'api_action_delete',
                'list'   => 'admin_geocode_index',
                'show'   => 'admin_geocode_show',
            ]
        );
    }

    /**
     * @Route("/trash", name="admin_geocode_trash", methods={"GET"})
     * @Route("/", name="admin_geocode_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function index(GeoCodeRepository $repository): Response
    {
        return $this->listOrTrash(
            $repository,
            [
                'trash' => 'findTrashForAdmin',
                'all'   => 'findAllForAdmin',
            ],
            'admin/geocode/index.html.twig',
            [
                'new'   => 'admin_geocode_new',
                'empty' => 'api_action_empty',
                'trash' => 'admin_geocode_trash',
                'list'  => 'admin_geocode_index',
            ],
            [
                'list'        => 'admin_geocode_index',
                'show'        => 'admin_geocode_show',
                'edit'        => 'admin_geocode_edit',
                'delete'      => 'api_action_delete',
                'trashdelete' => 'admin_geocode_destroy',
            ]
        );
    }

    /**
     * @Route("/new", name="admin_geocode_new", methods={"GET","POST"})
     */
    public function new(GeoCodeRequestHandler $requestHandler): Response
    {
        return $this->create(
            new GeoCode(),
            GeoCodeType::class,
            $requestHandler,
            ['list' => 'admin_geocode_index']
        );
    }

    /**
     * @Route("/{id}", name="admin_geocode_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_geocode_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(GeoCode $geoCode): Response
    {
        return $this->renderShowOrPreview(
            $geoCode,
            'admin/geocode/show.html.twig',
            [
                'delete'  => 'api_action_delete',
                'restore' => 'api_action_restore',
                'destroy' => 'api_action_destroy',
                'list'    => 'admin_geocode_index',
                'edit'    => 'admin_geocode_edit',
                'trash'   => 'admin_geocode_trash',
            ]
        );
    }
}
