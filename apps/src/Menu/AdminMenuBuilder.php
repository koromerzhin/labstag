<?php

namespace Labstag\Menu;

use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminMenuBuilder
{
    use MenuTrait;

    public function createMainMenu(RequestStack $requestStack): ItemInterface
    {
        unset($requestStack);
        $menu = $this->factory->createItem('menulabstag');
        $menu->setChildrenAttribute('class', 'navbar-nav mr-auto');
        $menu = $this->setData($menu, 'admin');

        return $menu;
    }
}
