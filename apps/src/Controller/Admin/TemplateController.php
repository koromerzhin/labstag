<?php

namespace Labstag\Controller\Admin;

use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Template;
use Labstag\Lib\AdminControllerLib;
use Labstag\Service\Admin\ViewService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/template', name: 'admin_template_')]
class TemplateController extends AdminControllerLib
{
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Template $template
    ): Response
    {
        return $this->setAdmin()->edit($template);
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
    public function preview(Template $template): Response
    {
        return $this->setAdmin()->preview($template);
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'])]
    public function show(Template $template): Response
    {
        return $this->setAdmin()->show($template);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/trash', name: 'trash', methods: ['GET'])]
    public function trash(): Response
    {
        return $this->setAdmin()->trash();
    }

    protected function setAdmin(): ViewService
    {
        return $this->adminService->setDomain(Template::class);
    }
}
