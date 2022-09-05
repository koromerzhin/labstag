<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Bookmark as EntityBookmark;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Bookmark;
use Labstag\Form\Admin\Paragraph\BookmarkType;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\BookmarkRepository;

class BookmarkParagraph extends ParagraphLib
{
    public function getEntity()
    {
        return Bookmark::class;
    }

    public function getForm()
    {
        return BookmarkType::class;
    }

    public function getName()
    {
        return $this->translator->trans('bookmark.name', [], 'paragraph');
    }

    public function getType()
    {
        return 'bookmark';
    }

    public function isShowForm()
    {
        return false;
    }

    public function show(Bookmark $bookmark)
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

    public function useIn()
    {
        return [
            Page::class,
        ];
    }
}
