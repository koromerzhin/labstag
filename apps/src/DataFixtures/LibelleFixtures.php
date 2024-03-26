<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Libelle;
use Labstag\Lib\DataFixtureLib;

class LibelleFixtures extends DataFixtureLib implements DependentFixtureInterface
{
    public function load(ObjectManager $objectManager): void
    {
        $generator = $this->setFaker();
        for ($index = 0; $index < self::NUMBER_LIBELLE; ++$index) {
            $this->addLibelle($generator, $index, $objectManager);
        }

        $objectManager->flush();
    }

    protected function addLibelle(
        Generator $generator,
        int $index,
        ObjectManager $objectManager
    ): void
    {
        $libelle = new Libelle();
        $libelle->setName($generator->unique()->colorName());

        $objectManager->persist($libelle);
        $this->addReference('libelle_'.$index, $libelle);
    }
}
