<?php

namespace Labstag\Block;

use Labstag\Entity\Block\Navbar;
use Labstag\Entity\Menu;
use Labstag\Form\Admin\Block\NavbarType;
use Labstag\Lib\BlockLib;
use Labstag\Service\MenuService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class NavbarBlock extends BlockLib
{
    public function __construct(
        protected MenuService $menuService,
        TranslatorInterface $translator,
        Environment $environment
    )
    {
        parent::__construct($translator, $environment);
    }

    public function getEntity(): string
    {
        return Navbar::class;
    }

    public function getForm(): string
    {
        return NavbarType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('navbar.name', [], 'block');
    }

    public function getType(): string
    {
        return 'navbar';
    }

    public function isShowForm(): bool
    {
        return true;
    }

    public function show(Navbar $navbar, $content): Response
    {
        $menu = $navbar->getMenu();
        $item = ($menu instanceof Menu) ? $this->menuService->createMenu($menu) : '';
        $show = (0 != count($item->getChildren()));

        unset($content);

        return $this->render(
            $this->getBlockFile('navbar'),
            [
                'show'  => $show,
                'item'  => $item,
                'block' => $navbar,
            ]
        );
    }
}