<?php

namespace Labstag\Controller;

use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Lib\FrontControllerLib;
use Labstag\Repository\ChapterRepository;
use Labstag\Repository\HistoryRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\RenderRepository;
use Labstag\Service\HistoryService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mes-histoires', name: 'front_history')]
class HistoryController extends FrontControllerLib
{
    #[Route(
        path: '/{slug}',
        name: '',
        priority: 2,
        defaults: ['slug' => '']
    )]
    public function bookmark(
        string $slug,
        HistoryRepository $historyRepository,
        PageRepository $pageRepository
    ): Response
    {
        $history = $historyRepository->findOneBy(
            ['slug' => $slug]
        );

        if (!$history instanceof History) {
            if ('' != $slug) {
                throw $this->createNotFoundException();
            }

            return $this->page('mes-histoires', $pageRepository);
        }

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $history]
        );
    }

    #[Route(
        path: '/{history}/{chapter}',
        name: '_chapter',
        priority: 2
    )]
    public function chapter(
        string $history,
        string $chapter,
        ChapterRepository $chapterRepository,
        HistoryRepository $historyRepository
    ): Response
    {
        /** @var History $history */
        $history = $historyRepository->findOneBy(
            ['slug' => $history]
        );

        /** @var Chapter $chapter */
        $chapter = $chapterRepository->findOneBy(
            ['slug' => $chapter]
        );

        /** @var History $chapterHistory */
        $chapterHistory = $chapter->getHistory();
        $test           = [
            !$history instanceof History,
            !$chapter instanceof Chapter,
            $chapterHistory->getId() !== $history->getId(),
        ];

        foreach ($test as $row) {
            if ($row) {
                throw $this->createNotFoundException();
            }
        }

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $chapter]
        );
    }

    #[Route(
        path: '/{slug}/pdf',
        name: '_pdf',
        priority: 3
    )]
    public function pdf(
        string $slug,
        HistoryService $historyService,
        HistoryRepository $historyRepository
    ): RedirectResponse
    {
        $history = $historyRepository->findOneBy(
            ['slug' => $slug]
        );

        $fileDirectory    = $this->getParameter('file_directory');
        $kernelProjectDir = $this->getParameter('kernel.project_dir');

        if (!$history instanceof History || !is_string($fileDirectory) || !is_string($kernelProjectDir)) {
            throw $this->createNotFoundException('Pas de fichier');
        }

        $historyService->process(
            (string) $fileDirectory,
            (string) $history->getId(),
            false
        );

        $filename = (string) $historyService->getFilename();

        $filename = str_replace(
            $kernelProjectDir.'/public/',
            '/',
            $filename
        );

        return new RedirectResponse($filename, 302);
    }

    #[Route(
        path: '/user/{username}',
        name: '_user',
        priority: 3
    )]
    public function user(
        string $username,
        RenderRepository $renderRepository
    ): Response
    {
        unset($username);
        $render = $renderRepository->findOneBy(
            ['url' => 'front_history_user']
        );

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $render]
        );
    }
}
