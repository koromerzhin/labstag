<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Labstag\Entity\Post;
use Labstag\Lib\FixtureLib;

class PostFixtures extends FixtureLib implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $this->add($manager);
    }

    protected function add(ObjectManager $manager): void
    {
        unset($manager);
        $faker = Factory::create('fr_FR');
        $statesTab = $this->getStates();
        for ($index = 0; $index < self::NUMBER_POST; ++$index) {
            $stateId = array_rand($statesTab);
            $states  = $statesTab[$stateId];
            $this->addPost($faker, $index, $states);
        }
    }

    public function getDependencies()
    {
        return [
            DataFixtures::class,
            UserFixtures::class,
            LibelleFixtures::class,
        ];
    }

    protected function addPost(
        Generator $faker,
        int $index,
        array $states
    ): void
    {
        $post    = new Post();
        $oldPost = clone $post;
        $post->setTitle($faker->unique()->colorName);
        $post->setContent($faker->unique()->sentence);
        $users   = $this->installService->getData('user');
        $indexUser = $faker->numberBetween(0, count($users)-1);
        $user      = $this->getReference('user_'.$indexUser);
        $post->setRefuser($user);
        $indexLibelle = $faker->numberBetween(0, self::NUMBER_LIBELLE-1);
        $libelle      = $this->getReference('libelle_'.$indexLibelle);
        $post->addLibelle($libelle);
        $post->setCommentaire((bool) rand(0, 1));
        $this->addReference('post_'. $index, $post);
        $this->templateRH->handle($oldPost, $post);
        $this->editoRH->changeWorkflowState($post, $states);
    }

    protected function getStates()
    {
        return [
            ['submit'],
            [
                'submit',
                'relire',
            ],
            [
                'submit',
                'relire',
                'corriger',
            ],
            [
                'submit',
                'relire',
                'publier',
            ],
            [
                'submit',
                'relire',
                'rejete',
            ],
        ];
    }
}
