<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\PhoneUser;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;

class PhoneUserFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            DataFixtures::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_PHONE, 'addPhone', $objectManager);
    }

    protected function addPhone(
        Generator $generator,
        int $index,
        array $states,
        ObjectManager $objectManager
    ): void
    {
        $users     = $this->installService->getData('user');
        $indexUser = $generator->numberBetween(0, (is_countable($users) ? count($users) : 0) - 1);
        /** @var User $user */
        $user      = $this->getReference('user_'.$indexUser);
        $number    = $generator->e164PhoneNumber;
        $phoneUser = new PhoneUser();
        $phoneUser->setRefuser($user);
        $phoneUser->setNumero($number);
        $phoneUser->setType($generator->word());
        $phoneUser->setCountry($generator->countryCode);
        $this->addReference('phone_'.$index, $phoneUser);
        $objectManager->persist($phoneUser);
        $this->workflowService->changeState($phoneUser, $states);
    }

    protected function getStatePhone(): array
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
