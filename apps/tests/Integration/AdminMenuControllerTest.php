<?php

namespace Labstag\Tests\Integration;

use Labstag\Entity\Menu;
use Labstag\Form\Admin\MenuType;
use Labstag\Repository\MenuRepository;
use Labstag\Tests\IntegrationTrait;
use Labstag\Tests\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Faker\Factory;

class AdminMenuControllerTest extends WebTestCase
{
    use LoginTrait;
    use IntegrationTrait;

    protected $urls = [
        'admin_menu_index',
        'admin_menu_new',
    ];

    protected $groupeDisable = [
        'visitor',
        'disable',
    ];

    /**
     * @dataProvider provideAllUrlWithoutParams
     * @param string $route
     */
    public function testUrl($route, $groupe)
    {
        $this->responseTest(
            $route,
            $groupe,
            !in_array($groupe, $this->groupeDisable)
        );
    }

    /**
     * @dataProvider getGroupes
     */
    public function testShow($groupe)
    {
        $this->showTest(
            $groupe,
            !in_array($groupe, $this->groupeDisable),
            'admin_menu_show'
        );
    }

    /**
     * @dataProvider getGroupes
     */
    public function testPost($groupe)
    {
        $this->editPostRedirect(
            $groupe,
            'admin_menu_edit',
            MenuType::class,
            !in_array($groupe, $this->groupeDisable)
        );
    }

    /**
     * @dataProvider getGroupes
     */
    public function testAdd($groupe)
    {
        $this->addNewEntity(
            $groupe,
            !in_array($groupe, $this->groupeDisable),
            'admin_menu_new',
            MenuType::class
        );
    }

    /**
     * @dataProvider getGroupes
     */
    public function testShowDataNotFound($groupe)
    {
        $this->showEditDataNotFound($groupe, 'admin_menu_show');
    }

    /**
     * @dataProvider getGroupes
     */
    public function testEditDataNotFound($groupe)
    {
        $this->showEditDataNotFound($groupe, 'admin_menu_edit');
    }

    /**
     * @dataProvider getGroupes
     */
    public function testDelete($groupe)
    {
        $this->editDelete(
            $groupe,
            !in_array($groupe, $this->groupeDisable),
            'admin_menu_delete'
        );
    }

    protected function getEntity($client)
    {
        $container     = $client->getContainer();
        $doctrine      = $container->get('doctrine');
        $entityManager = $doctrine->getManager();
        $repository    = $entityManager->getRepository(Menu::class);
        /** @var MenuRepository $repository */
        $data = $repository->findOneRandom();

        return $data;
    }

    protected function getNewEntity()
    {
        $faker = Factory::create('fr_FR');
        $menu  = new Menu();
        $menu->setPosition(50);
        $menu->setClef($faker->unique()->word());

        return $menu;
    }
}
