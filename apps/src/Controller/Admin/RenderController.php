<?php

namespace Labstag\Controller\Admin;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Render;
use Labstag\Lib\AdminControllerLib;
use Labstag\Service\Admin\ViewService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/render', name: 'admin_render_')]
class RenderController extends AdminControllerLib
{
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Render $render
    ): Response
    {
        return $this->setAdmin()->edit($render);
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->setAdmin()->index();
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(): Response
    {
        return $this->setAdmin()->new();
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/preview/{id}', name: 'preview', methods: ['GET'])]
    public function preview(Render $render): Response
    {
        return $this->setAdmin()->preview($render);
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'])]
    public function show(Render $render): Response
    {
        return $this->setAdmin()->show($render);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/trash', name: 'trash', methods: ['GET'])]
    public function trash(): Response
    {
        return $this->setAdmin()->trash();
    }

    protected function setAdmin(): ViewService
    {
        return $this->adminService->setDomain(Render::class);
    }
}
