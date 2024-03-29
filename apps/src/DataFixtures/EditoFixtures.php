<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Edito;
use Labstag\Entity\Meta;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;
use Labstag\Repository\UserRepository;

class EditoFixtures extends FixtureLib implements DependentFixtureInterface
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
        $this->loadForeach(self::NUMBER_EDITO, 'addEdito', $objectManager);
    }

    protected function addEdito(
        Generator $generator,
        int $index,
        array $states,
        ObjectManager $objectManager
    ): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $objectManager->getRepository(User::class);
        $users          = $userRepository->findAll();
        $edito          = new Edito();
        $meta           = new Meta();
        $meta->setEdito($edito);
        $this->setMeta($meta);
        $random = $generator->numberBetween(5, 50);
        $edito->setTitle($generator->unique()->text($random));
        /** @var string $content */
        $content = $generator->paragraphs(random_int(4, 10), true);
        $edito->setContent(str_replace("\n\n", "<br />\n", (string) $content));
        $edito->setPublished($generator->unique()->dateTime('now'));
        $this->addReference('edito_'.$index, $edito);
        $tabIndex = array_rand($users);
        /** @var User $user */
        $user = $users[$tabIndex];
        $edito->setRefuser($user);
        $this->upload($edito, $generator);
        $objectManager->persist($edito);
        $this->workflowService->changeState($edito, $states);
    }
}
