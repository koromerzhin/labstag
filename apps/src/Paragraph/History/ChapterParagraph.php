<?php

namespace Labstag\Paragraph\History;

use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\History\Chapter as HistoryChapter;
use Labstag\Form\Admin\Paragraph\History\ChapterType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\ChapterRepository;
use Symfony\Component\HttpFoundation\Request;

class ChapterParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $all        = $request->attributes->all();
        $routeParam = $all['_route_params'];
        $history    = $routeParam['history'] ?? null;
        $chapter    = $routeParam['chapter'] ?? null;
        /** @var ChapterRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Chapter::class);
        $chapter       = $repositoryLib->findChapterByHistory($history, $chapter);
        if (!$chapter instanceof Chapter) {
            return null;
        }

        /** @var History $history */
        $history  = $chapter->getHistory();
        $prevnext = $this->getPrevNext($chapter, $history);

        return [
            'prev'      => $prevnext['prev'],
            'next'      => $prevnext['next'],
            'chapter'   => $chapter,
            'history'   => $chapter->getHistory(),
            'paragraph' => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['history/chapter'];
    }

    public function getEntity(): string
    {
        return HistoryChapter::class;
    }

    public function getForm(): string
    {
        return ChapterType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('historychapter.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'historychapter';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    /**
     * @return array<class-string<Layout>>
     */
    public function useIn(): array
    {
        return [
            Layout::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getPrevNext(
        Chapter $chapter,
        History $history
    ): array
    {
        $chapters = $history->getchapters();
        $prev     = null;
        $next     = null;
        foreach ($chapters as $i => $row) {
            /** @var Chapter $row */
            if ($row->getSlug() === $chapter->getSlug()) {
                $prev    = $chapters[$i - 1] ?? null;
                $next    = $chapters[$i + 1] ?? null;
                $chapter = $row;

                break;
            }
        }

        return [
            'prev' => $prev,
            'next' => $next,
        ];
    }
}
