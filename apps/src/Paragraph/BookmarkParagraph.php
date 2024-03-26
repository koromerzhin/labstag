<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Bookmark as EntityBookmark;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Bookmark;
use Labstag\Form\Admin\Paragraph\BookmarkType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\BookmarkRepository;

class BookmarkParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var BookmarkRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(EntityBookmark::class);
        $bookmarks     = $repositoryLib->getLimitOffsetResult(
            $repositoryLib->findPublier(),
            5,
            0
        );

        return [
            'paragraph' => $entityParagraph,
            'bookmarks' => $bookmarks,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['bookmark'];
    }

    public function getEntity(): string
    {
        return Bookmark::class;
    }

    public function getForm(): string
    {
        return BookmarkType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('bookmark.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'bookmark';
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
