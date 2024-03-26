<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Block;
use Labstag\Entity\Block\Navbar;
use Labstag\Entity\Menu;
use Labstag\Lib\FixtureLib;
use Labstag\Service\BlockService;

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
        $this->addSubBlock($block, $blockData);
        $objectManager->persist($block);
        $this->addReference('block_'.$region.'-'.$type, $block);
        if (array_key_exists('notinpages', $blockData)) {
            $this->enqueueMethod->async(
                BlockService::class,
                'process',
                [
                    'region'     => $region,
                    'position'   => $position + 1,
                    'notinpages' => $blockData['notinpages'],
                ]
            );
        }
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

    protected function addSubBlock(
        Block $block,
        array $blockData
    ): void
    {
        $classentity = $this->blockService->getTypeEntity($block);
        $entity      = $this->blockService->getEntity($block);
        if (!is_null($entity) || is_null($classentity)) {
            return;
        }

        $entity = new $classentity();
        $entity->setBlock($block);
        if (array_key_exists('code-menu', $blockData)) {
            /** @var Menu $menu */
            $menu = $this->getReference('menu_'.$blockData['code-menu']);

            /** @var Navbar $entity */
            $entity->setMenu($menu);
        }

        $this->blockService->setEntity($block, $entity);
    }
}
