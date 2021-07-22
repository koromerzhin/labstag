<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Lib\FixtureLib;

class GroupFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [DataFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        unset($manager);
        $groupes = $this->installService->getData('group');
        foreach ($groupes as $key => $row) {
            $this->addGroupe($key, $row);
        }
    }
}
