<?php

namespace Labstag\Controller;

use Exception;
use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Configuration;
use Labstag\Lib\AdminControllerLib;
use Labstag\Service\Admin\Entity\AdminService;
use Labstag\Service\Admin\Entity\ConfigurationService;
use Labstag\Service\Admin\Entity\TrashService as EntityTrashService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin')]
class AdminController extends AdminControllerLib
{
    #[Route(path: '/export', name: 'admin_export')]
    public function export(): RedirectResponse
    {
        $configurationService = $this->paramConfig();

        return $configurationService->export();
    }

    #[Route(path: '/paragraph', name: 'admin_paragraph', methods: ['GET'])]
    public function iframe(): Response
    {
        return $this->render('admin/paragraph/iframe.html.twig');
    }

    #[Route(path: '/', name: 'admin')]
    public function index(): Response
    {
        return $this->adminConfig()->home();
    }

    #[Route(path: '/oauth', name: 'admin_oauth')]
    public function oauth(): Response
    {
        return $this->adminConfig()->oauth();
    }

    #[Route(path: '/param', name: 'admin_param', methods: ['GET', 'POST'])]
    public function param(): Response
    {
        $configurationService = $this->paramConfig();

        return $configurationService->form();
    }

    #[Route(path: '/themes/{state}', name: 'admin_themes', defaults: ['state' => 'admin'])]
    public function themes(string $state): Response
    {
        return $this->adminConfig()->themes($state);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/trash', name: 'admin_trash')]
    public function trash(): Response
    {
        $viewService = $this->adminService->setDomain('trash');
        if (!$viewService instanceof EntityTrashService) {
            throw new Exception('TrashService not found');
        }

        return $viewService->list();
    }

    private function adminConfig(): AdminService
    {
        $viewService = $this->adminService->setDomain('admin');
        if (!$viewService instanceof AdminService) {
            throw new Exception('AdminService not found');
        }

        return $viewService;
    }

    private function paramConfig(): ConfigurationService
    {
        $viewService = $this->adminService->setDomain(Configuration::class);
        if (!$viewService instanceof ConfigurationService) {
            throw new Exception('ConfigurationService not found');
        }

        return $viewService;
    }
}
