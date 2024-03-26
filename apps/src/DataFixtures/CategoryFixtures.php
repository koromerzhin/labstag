<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Lib\DataFixtureLib;

class CategoryFixtures extends DataFixtureLib implements DependentFixtureInterface
{
    public function load(ObjectManager $objectManager): void
    {
        $generator = $this->setFaker();
        for ($index = 0; $index < self::NUMBER_CATEGORY; ++$index) {
            $this->addCategory($generator, $index, $objectManager);
        }

        $objectManager->flush();
    }

    protected function addCategory(
        Generator $generator,
        int $index,
        ObjectManager $objectManager
    ): Category
    {
        $category = new Category();
        $category->setName($generator->unique()->colorName());

        $indexCategory = $generator->numberBetween(0, $index);
        $code          = 'category_'.$indexCategory;
        if ($this->hasReference($code) && 1 == random_int(0, 1)) {
            /** @var Category $parent */
            $parent = $this->getReference($code);
            $category->setParent($parent);
        }

        $objectManager->persist($category);
        $this->addReference('category_'.$index, $category);

        return $category;
    }
}
