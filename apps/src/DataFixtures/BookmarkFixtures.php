<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Bookmark;
use Labstag\Entity\Category;
use Labstag\Entity\User;
use Labstag\Lib\FixtureLib;

class BookmarkFixtures extends FixtureLib implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return $this->getDependenciesBookmarkPost();
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_BOOKMARK, 'addLink', $objectManager);
    }

    protected function addLink(
        Generator $generator,
        int $index,
        array $states,
        ObjectManager $objectManager
    ): void
    {
        $bookmark = new Bookmark();
        $this->setLibelles(
            generator: $generator,
            bookmark: $bookmark
        );
        $users     = $this->installService->getData('user');
        $indexUser = $generator->numberBetween(0, (is_countable($users) ? count($users) : 0) - 1);
        /** @var User $user */
        $user = $this->getReference('user_'.$indexUser);
        $bookmark->setRefuser($user);
        /** @var string $content */
        $content = $generator->paragraphs(random_int(4, 10), true);
        $bookmark->setContent(str_replace("\n\n", "<br />\n", (string) $content));
        $indexLibelle = $generator->numberBetween(0, self::NUMBER_CATEGORY - 1);
        /** @var Category $category */
        $category = $this->getReference('category_'.$indexLibelle);
        $bookmark->setRefcategory($category);
        $bookmark->setName($generator->word());
        $bookmark->setUrl($generator->url());
        $bookmark->setPublished($generator->unique()->dateTime('now'));
        $this->upload($bookmark, $generator);
        $objectManager->persist($bookmark);
        $this->addReference('bookmark_'.$index, $bookmark);
        $this->workflowService->changeState($bookmark, $states);
    }
}
