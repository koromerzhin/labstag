<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Entity\Groupe;
use Labstag\Lib\DataFixtureLib;

class GroupFixtures extends DataFixtureLib implements DependentFixtureInterface
{
    public function load(ObjectManager $objectManager): void
    {
        $groupes = $this->installService->getData('group');
        foreach ($groupes as $key => $row) {
            $this->addGroupe($key, $row, $objectManager);
        }

        $objectManager->flush();
    }

    protected function addGroupe(
        int $key,
        string $row,
        ObjectManager $objectManager
    ): void
    {
        $groupe = new Groupe();
        $groupe->setCode($row);
        $groupe->setName($row);

        $objectManager->persist($groupe);
        $this->addReference('groupe_'.$key, $groupe);
    }
}
