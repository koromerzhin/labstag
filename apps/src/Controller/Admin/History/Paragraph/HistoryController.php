<?php

namespace Labstag\Controller\Admin\History\Paragraph;

use Labstag\Entity\History;
use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphControllerLib;
use Labstag\Repository\ParagraphRepository;
use Labstag\RequestHandler\ParagraphRequestHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/history/paragraph')]
class HistoryController extends ParagraphControllerLib
{
    #[Route(path: '/add/{id}', name: 'admin_history_paragraph_add')]
    public function add(
        ParagraphRequestHandler $handler,
        History $history,
        ParagraphRepository $repository,
        Request $request
    ): RedirectResponse
    {
        $paragraph = new Paragraph();
        $old       = clone $paragraph;
        $paragraph->setPosition(count($history->getParagraphs()) + 1);
        $paragraph->setHistory($history);
        $paragraph->settype($request->get('data'));
        $repository->add($paragraph);
        $handler->handle($old, $paragraph);

        return $this->redirectToRoute('admin_history_paragraph_list', ['id' => $history->getId()]);
    }

    #[Route(path: '/delete/{id}', name: 'admin_history_paragraph_delete')]
    public function delete(Paragraph $paragraph): Response
    {
        return $this->deleteParagraph(
            $paragraph,
            $paragraph->getHistory(),
            'admin_history_edit'
        );
    }

    #[Route(path: '/', name: 'admin_history_paragraph_index', methods: ['GET'])]
    public function iframe()
    {
        return $this->render('admin/paragraph/iframe.html.twig');
    }

    #[Route(path: '/list/{id}', name: 'admin_history_paragraph_list')]
    public function list(History $history): Response
    {
        return $this->listTwig(
            'admin_history_paragraph_show',
            $history->getParagraphs(),
            'admin_history_paragraph_delete'
        );
    }

    #[Route(path: '/show/{id}', name: 'admin_history_paragraph_show')]
    public function show(Paragraph $paragraph, ParagraphRequestHandler $handler)
    {
        return parent::showTwig($paragraph, $handler);
    }
}