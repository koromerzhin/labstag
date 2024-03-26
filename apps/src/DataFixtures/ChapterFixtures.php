<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Entity\Meta;
use Labstag\Lib\FixtureLib;

class ChapterFixtures extends FixtureLib implements DependentFixtureInterface
{

    protected array $position = [];

    public function getDependencies(): array
    {
        return [
            DataFixtures::class,
            UserFixtures::class,
            HistoryFixtures::class,
        ];
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->loadForeach(self::NUMBER_HISTORY, 'addChapter', $objectManager);
    }

    protected function addChapter(
        Generator $generator,
        int $index,
        array $states,
        ObjectManager $objectManager
    ): void
    {
        $chapter = new Chapter();
        $meta    = new Meta();
        $meta->setChapter($chapter);
        $this->setMeta($meta);
        $chapter->setName($generator->unique()->colorName());
        /** @var string $content */
        $content = $generator->paragraphs(random_int(4, 10), true);
        $chapter->setContent(str_replace("\n\n", "<br />\n", (string) $content));
        $indexHistory = $generator->numberBetween(0, self::NUMBER_HISTORY - 1);
        /** @var History $history */
        $history = $this->getReference('history_'.$indexHistory);
        if (!isset($this->position[$indexHistory])) {
            $this->position[$indexHistory] = [];
        }

        $chapter->setHistory($history);
        $chapter->setPublished($generator->unique()->dateTime('now'));

        $indexposition = $this->position[$indexHistory];
        $position      = is_countable($indexposition) ? count($indexposition) : 0;
        $chapter->setPosition($position + 1);
        $this->position[$indexHistory][] = $chapter;
        $objectManager->persist($chapter);
        $this->addReference('chapter_'.$index, $chapter);
        $this->workflowService->changeState($chapter, $states);
    }
}
