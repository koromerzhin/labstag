<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Labstag\Lib\FixtureLib;

class PhoneUserFixtures extends FixtureLib implements DependentFixtureInterface
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
        $faker     = $this->setFaker();
        $statesTab = $this->getStates();
        $users     = $this->installService->getData('user');
        for ($index = 0; $index < self::NUMBER_PHONE; ++$index) {
            $indexUser = $faker->numberBetween(0, count($users) - 1);
            $stateId   = array_rand($statesTab);
            $states    = $statesTab[$stateId];
            $user      = $this->getReference('user_'.$indexUser);
            $this->addPhone($faker, $user, $states);
        }
    }

    protected function getStates()
    {
        return [
            ['submit'],
            [
                'submit',
                'valider',
            ],
        ];
    }
}
