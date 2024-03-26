<?php

namespace Labstag\Paragraph;

use Labstag\Entity\History as EntityHistory;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\History;
use Labstag\Form\Admin\Paragraph\HistoryType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\HistoryRepository;

class HistoryParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var HistoryRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(EntityHistory::class);
        $histories     = $repositoryLib->getLimitOffsetResult(
            $repositoryLib->findPublier(),
            5,
            0
        );

        return [
            'histories' => $histories,
            'paragraph' => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['history'];
    }

    public function getEntity(): string
    {
        return History::class;
    }

    public function getForm(): string
    {
        return HistoryType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('history.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'history';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function useIn(): array
    {
        return [
            Page::class,
        ];
    }
}
