<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Block;
use Labstag\Entity\Block\Navbar;
use Labstag\Entity\Menu;
use Labstag\Lib\FixtureLib;

class BlockFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            DataFixtures::class,
            MenuFixtures::class,
        ];
    }

    public function load(ObjectManager $objectManager): void
    {
        $json = $this->installService->getData('data/block');
        foreach ($json as $data) {
            $this->addBlocks($data['region'], $data['blocks'], $objectManager);
        }

        $objectManager->flush();
    }

    protected function addBlock(
        string $region,
        int $position,
        array $blockData,
        ObjectManager $objectManager
    ): void
    {
        $type  = $blockData['type'];
        $block = new Block();
        $block->setTitle($region.' - '.$type.'('.($position + 1).')');
        $block->setRegion($region);
        $block->setType($type);
        $block->setPosition($position + 1);
        if (array_key_exists('code-menu', $blockData)) {
            /** @var Menu $menu */
            $menu        = $this->getReference('menu_'.$blockData['code-menu']);
            $classentity = $this->blockService->getTypeEntity($block);
            $entity      = $this->blockService->getEntity($block);
            if (!is_null($entity) || is_null($classentity)) {
                return;
            }

            /** @var Navbar $entity */
            $entity = new $classentity();
            $entity->setBlock($block);
            $entity->setMenu($menu);
            $block->addMenu($entity);
        }

        $this->addReference('block_'.$region.'-'.$type, $block);

        $objectManager->persist($block);
    }

    protected function addBlocks(
        string $region,
        array $blocks,
        ObjectManager $objectManager
    ): void
    {
        foreach ($blocks as $position => $block) {
            $this->addBlock($region, $position, $block, $objectManager);
        }
    }
}
