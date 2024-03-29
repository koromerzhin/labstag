<?php

namespace Labstag\Paragraph\History;

use Labstag\Entity\History;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\History\Liste;
use Labstag\Form\Admin\Paragraph\History\ListType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\HistoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListParagraph extends ParagraphLib implements ParagraphInterface
{
    public function getCode(EntityParagraphInterface $entityParagraph): string
    {
        unset($entityParagraph);

        return 'history/list';
    }

    public function getEntity(): string
    {
        return Liste::class;
    }

    public function getForm(): string
    {
        return ListType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('historylist.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'historylist';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function show(EntityParagraphInterface $entityParagraph): Response
    {
        /** @var HistoryRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(History::class);
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        $pagination = $this->paginator->paginate(
            $repositoryLib->findPublier(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            $this->getTemplateFile($this->getCode($entityParagraph)),
            [
                'pagination' => $pagination,
                'paragraph'  => $entityParagraph,
            ]
        );
    }

    public function useIn(): array
    {
        return [
            Page::class,
        ];
    }
}
