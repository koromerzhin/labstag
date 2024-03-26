<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Page;
use Labstag\Lib\FixtureLib;

class PageFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            DataFixtures::class,
        ];
    }

    public function load(ObjectManager $objectManager): void
    {
        $data = $this->installService->getData('data/page');
        foreach ($data as $page) {
            $this->addPage($page, $objectManager, null);
        }

        $objectManager->flush();
    }

    protected function addPage(
        array $pageData,
        ObjectManager $objectManager,
        ?Page $parent = null
    ): void
    {
        $page = new Page();
        $page->setName($pageData['name']);
        if (!is_null($parent)) {
            $page->setParent($parent);
        }

        if (isset($pageData['paragraphs'])) {
            $this->addParagraphs($page, $pageData['paragraphs'], $objectManager);
        }

        $reference = 'page_'.$pageData['slug'];
        $objectManager->persist($page);
        $this->addReference($reference, $page);
        if (!array_key_exists('pages', $pageData)) {
            return;
        }

        foreach ($pageData['pages'] as $data) {
            $this->addPage($data, $objectManager, $page);
        }
    }
}
