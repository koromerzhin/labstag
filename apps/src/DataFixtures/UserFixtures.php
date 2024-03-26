<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Labstag\Repository\GroupeRepository;

class UserFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            DataFixtures::class,
            GroupFixtures::class,
        ];
    }

    public function load(ObjectManager $objectManager): void
    {
        $generator = $this->setFaker();
        $users     = $this->installService->getData('user');
        /** @var GroupeRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Groupe::class);
        $groupes       = $repositoryLib->findAll();
        foreach ($users as $index => $user) {
            $this->addUser($groupes, $index, $user, $generator, $objectManager);
        }

        $objectManager->flush();
    }

    protected function addUser(
        array $groupes,
        int $index,
        array $dataUser,
        Generator $generator,
        ObjectManager $objectManager
    ): void
    {
        $user = new User();

        $user->setRefgroupe($this->getGroupe($groupes, $dataUser['groupe']));
        $user->setUsername($dataUser['username']);
        $user->setPlainPassword($dataUser['password']);
        $user->setEmail($dataUser['email']);

        $this->upload($user, $generator);
        $objectManager->persist($user);
        $this->addReference('user_'.$index, $user);
        $this->workflowService->changeState($user, $dataUser['state']);
    }
}
