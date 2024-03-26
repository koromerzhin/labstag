<?php

namespace Labstag\Command;

use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Render;
use Labstag\Lib\CommandLib;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\EditoRepository;
use Labstag\Repository\HistoryRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use Labstag\Repository\RenderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:metatags:correction'
)]
class LabstagMetatagsCorrectionCommand extends CommandLib
{
    public function executeChapter(): void
    {
        /** @var ChapterRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Chapter::class);
        $all           = $repositoryLib->findAll();
        foreach ($all as $entity) {
            /** @var Chapter $entity */
            $repositoryLib->save($entity);
        }
    }

    public function executeEdito(): void
    {
        /** @var EditoRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Edito::class);
        $all           = $repositoryLib->findAll();
        foreach ($all as $entity) {
            /** @var Edito $entity */
            $repositoryLib->save($entity);
        }
    }

    public function executeHistory(): void
    {
        /** @var HistoryRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(History::class);
        $all           = $repositoryLib->findAll();
        foreach ($all as $entity) {
            /** @var History $entity */
            $repositoryLib->save($entity);
        }
    }

    public function executePage(): void
    {
        /** @var PageRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Page::class);
        $all           = $repositoryLib->findAll();
        foreach ($all as $entity) {
            /** @var Page $entity */
            $repositoryLib->save($entity);
        }
    }

    public function executePost(): void
    {
        /** @var PostRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Post::class);
        $all           = $repositoryLib->findAll();
        foreach ($all as $entity) {
            /** @var Post $entity */
            $repositoryLib->save($entity);
        }
    }

    public function executeRender(): void
    {
        /** @var RenderRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Render::class);
        $all           = $repositoryLib->findAll();
        foreach ($all as $entity) {
            /** @var Render $entity */
            $repositoryLib->save($entity);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $functions    = [
            'executeChapter',
            'executeEdito',
            'executeHistory',
            'executePage',
            'executePost',
            'executeRender',
        ];
        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            call_user_func($callable);
        }

        $symfonyStyle->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
