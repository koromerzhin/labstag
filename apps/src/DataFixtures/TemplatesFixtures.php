<?php

namespace Labstag\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Labstag\Entity\Template;
use Labstag\Lib\FixtureLib;

class TemplatesFixtures extends FixtureLib implements DependentFixtureInterface
{
    const NUMBER = 10;

    public function load(ObjectManager $manager): void
    {
        $this->add($manager);
    }

    protected function add(ObjectManager $manager): void
    {
        unset($manager);
        $faker = Factory::create('fr_FR');
        for ($index = 0; $index < self::NUMBER; ++$index) {
            $this->addTemplate($faker);
        }
    }

    public function getDependencies()
    {
        return [DataFixtures::class];
    }

    protected function addTemplate(Generator $faker): void
    {
        $template    = new Template();
        $oldTemplate = clone $template;
        $template->setName($faker->unique()->colorName);
        /** @var string $content */
        $content = $faker->unique()->paragraphs(10, true);
        $template->setHtml(str_replace("\n\n", '<br />', $content));
        $template->setText($content);
        $this->templateRH->handle($oldTemplate, $template);
    }
}
