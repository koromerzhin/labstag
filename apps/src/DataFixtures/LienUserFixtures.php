<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Labstag\Lib\FixtureLib;

class LienUserFixtures extends FixtureLib implements DependentFixtureInterface
{
    public const NUMBER = 25;

    public function load(ObjectManager $manager): void
    {
        unset($manager);
        $faker = Factory::create('fr_FR');
        for ($index = 0; $index < self::NUMBER; ++$index) {
            $indexUser = $faker->numberBetween(1, 3);
            $user      = $this->getReference('user_'.$indexUser);
            $this->addLink($faker, $user);
        }
    }

    public function getDependencies()
    {
        return [
            DataFixtures::class,
            UserFixtures::class,
        ];
    }
}
