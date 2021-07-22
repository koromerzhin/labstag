<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\{
    DependentFixtureInterface as DependentInterface
};
use Doctrine\Persistence\ObjectManager;
use Labstag\Lib\FixtureLib;

class AdresseUserFixtures extends FixtureLib implements DependentInterface
{
    public function getDependencies()
    {
        return [
            DataFixtures::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        unset($manager);
        $faker = $this->setFaker();
        $users = $this->installService->getData('user');
        for ($index = 0; $index < self::NUMBER_ADRESSE; ++$index) {
            $indexUser = $faker->numberBetween(0, count($users) - 1);
            $user      = $this->getReference('user_'.$indexUser);
            $this->addAdresse($faker, $user);
        }
    }
}
