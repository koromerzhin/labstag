<?php

namespace Labstag\Controller\Admin\History\Paragraph;

use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphControllerLib;
use Labstag\RequestHandler\ParagraphRequestHandler;
use Labstag\Service\ParagraphService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/history/chapter/paragraph', name: 'admin_chapter_paragraph_')]
class ChapterController extends ParagraphControllerLib
{
    #[Route(path: '/add/{id}', name: 'add')]
    public function add(
        ParagraphService $paragraphService,
        Chapter $chapter,
        Request $request
    ): RedirectResponse
    {
        $data = $request->get('data');
        if (!is_string($data)) {
            throw new Exception('data is not string');
        }

        $paragraphService->add($chapter, $data);

        return $this->redirectToRoute('admin_chapter_paragraph_list', ['id' => $chapter->getId()]);
    }

    #[Route(path: '/delete/{id}', name: 'delete')]
    public function delete(Paragraph $paragraph): Response
    {
        return $this->deleteParagraph(
            $paragraph,
            $paragraph->getChapter(),
            'admin_chapter_edit'
        );
    }

    #[Route(path: '/list/{id}', name: 'list')]
    public function list(Chapter $chapter): Response
    {
        return $this->listTwig(
            'admin_chapter_paragraph_show',
            $chapter->getParagraphs(),
            'admin_chapter_paragraph_delete'
        );
    }

    #[Route(path: '/show/{id}', name: 'show')]
    public function show(
        Paragraph $paragraph,
        ParagraphRequestHandler $paragraphRequestHandler
    ): Response
    {
        return parent::showTwig($paragraph, $paragraphRequestHandler);
    }
}
