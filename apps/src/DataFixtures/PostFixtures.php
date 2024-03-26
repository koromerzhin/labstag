<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Category;
use Labstag\Entity\Meta;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;

class PostFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return $this->getDependenciesBookmarkPost();
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_POST, 'addPost', $objectManager);
    }

    protected function addPost(
        Generator $generator,
        int $index,
        array $states,
        ObjectManager $objectManager
    ): void
    {
        $post = new Post();
        $meta = new Meta();
        $meta->setPost($post);
        $this->setMeta($meta);
        $post->setTitle($generator->unique()->colorName());
        /** @var string $content */
        $content = $generator->paragraphs(random_int(4, 10), true);
        $post->setContent(str_replace("\n\n", "<br />\n", (string) $content));
        $users     = $this->installService->getData('user');
        $indexUser = $generator->numberBetween(0, (is_countable($users) ? count($users) : 0) - 1);
        /** @var User $user */
        $user = $this->getReference('user_'.$indexUser);
        $post->setRefuser($user);
        $post->setPublished($generator->unique()->dateTime('now'));

        $indexLibelle = $generator->numberBetween(0, self::NUMBER_CATEGORY - 1);
        /** @var Category $category */
        $category = $this->getReference('category_'.$indexLibelle);
        $post->setRefcategory($category);
        $post->setRemark((bool) random_int(0, 1));
        $this->upload($post, $generator);
        $objectManager->persist($post);
        $this->addReference('post_'.$index, $post);
        $this->setLibelles(
            generator: $generator,
            post: $post
        );
        $this->workflowService->changeState($post, $states);
    }
}
