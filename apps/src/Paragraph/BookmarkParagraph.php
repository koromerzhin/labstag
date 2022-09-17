<?php

namespace Labstag\Paragraph;

use Symfony\Component\HttpFoundation\Response;
use Labstag\Entity\Bookmark as EntityBookmark;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Bookmark;
use Labstag\Form\Admin\Paragraph\BookmarkType;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\BookmarkRepository;

class BookmarkParagraph extends ParagraphLib
{
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

    public function show(Bookmark $bookmark): Response
    {
        /** @var BookmarkRepository $repository */
        $repository = $this->getRepository(EntityBookmark::class);
        $bookmarks  = $repository->getLimitOffsetResult($repository->findPublier(), 5, 0);

        return $this->render(
            $this->getParagraphFile('bookmark'),
            [
                'paragraph' => $bookmark,
                'bookmarks' => $bookmarks,
            ]
        );
    }

    /**
     * @return array<class-string<Page>>
     */
    public function useIn(): array
    {
        return [
            Page::class,
        ];
    }
}
