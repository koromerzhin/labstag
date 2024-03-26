<?php

namespace Labstag\Service\Admin\Entity;

use Exception;
use Labstag\Entity\Menu;
use Labstag\Interfaces\AdminEntityServiceInterface;
use Labstag\Repository\MenuRepository;
use Labstag\Service\Admin\ViewService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuService extends ViewService implements AdminEntityServiceInterface
{
    public function add(array $parameters = []): Response
    {
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $routes  = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['list'])) {
            throw new Exception('Route list not found');
        }

        $get = $request->query->all();
        $url = $this->generateUrl($routes['list']);
        if (!isset($get['id'])) {
            return new RedirectResponse($url);
        }

        /** @var MenuRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Menu::class);
        $parent        = $repositoryLib->find($get['id']);
        if (!$parent instanceof Menu) {
            return new RedirectResponse($url);
        }

        $menu = new Menu();
        $data = [$menu->getData()];
        $menu->setClef(null);
        $menu->setData($data);
        $menu->setSeparateur(false);

        $children = $parent->getChildren();
        $position = is_countable($children) ? count($children) : 0;
        $menu->setPosition($position + 1);
        $menu->setParent($parent);

        return $this->editOrNew('edit', $menu, $parameters);
    }

    public function divider(Menu $menu): RedirectResponse
    {
        $routes = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['list'])) {
            throw new Exception('Route list not found');
        }

        $entity   = new Menu();
        $children = $menu->getChildren();
        $position = is_countable($children) ? count($children) : 0;
        $entity->setPosition($position + 1);
        $entity->setSeparateur(true);
        $entity->setParent($menu);

        /** @var MenuRepository $menuRepository */
        $menuRepository = $this->repositoryService->get(Menu::class);
        $menuRepository->save($entity);

        return new RedirectResponse(
            $this->generateUrl($routes['list'])
        );
    }

    public function getType(): string
    {
        return Menu::class;
    }

    public function index(
        array $parameters = []
    ): Response
    {
        /** @var MenuRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Menu::class);
        $all           = $repositoryLib->findAllCode();
        $globals       = $this->twigEnvironment->getGlobals();
        $modal         = $globals['modal'] ?? [];
        if (!is_array($modal)) {
            $modal = [];
        }

        $modal['delete'] = true;
        $this->twigEnvironment->addGlobal('modal', $modal);
        $this->btnService->addBtnNew('admin_menu_new');

        $templates = $this->getDomain()->getTemplates();
        if (!array_key_exists('index', $templates)) {
            throw new Exception('Template not found');
        }

        $parameters = [
            ...$parameters,
            'all' => $all,
        ];

        return $this->render(
            'admin/menu/index.html.twig',
            $parameters
        );
    }

    public function move(
        Menu $menu,
        array $parameters = []
    ): Response
    {
        $routes = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['list']) || !isset($routes['position'])) {
            throw new Exception('Route list not found');
        }

        /** @var Request $request */
        $request    = $this->requeststack->getCurrentRequest();
        $currentUrl = $this->generateUrl(
            $routes['position'],
            [
                'id' => $menu->getId(),
            ]
        );
        if ('POST' == $request->getMethod()) {
            $this->setPositionEntity($request, Menu::class);
        }

        $this->btnService->addBtnList(
            $routes['list'],
            'Liste',
        );
        $this->btnService->add(
            'btn-admin-save-move',
            'Enregistrer',
            [
                'is'   => 'link-btnadminmove',
                'href' => $currentUrl,
            ]
        );

        $parameters = [
            ...$parameters,
            'menu' => $menu,
        ];

        $templates = $this->getDomain()->getTemplates();
        if (!array_key_exists('move', $templates)) {
            throw new Exception('Template not found');
        }

        return $this->render(
            $templates['move'],
            $parameters
        );
    }
}
