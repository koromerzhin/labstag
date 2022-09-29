<?php

namespace Labstag\Paragraph\Post;

use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Post\Archive;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\Post\ArchiveType;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;

class ArchiveParagraph extends ParagraphLib
{
    public function getEntity(): string
    {
        return Archive::class;
    }

    public function getForm(): string
    {
        return ArchiveType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('postarchive.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'postarchive';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function show(Archive $archive): Response
    {
        /** @var PostRepository $entityRepository */
        $entityRepository = $this->getRepository(Post::class);
        $archives         = $entityRepository->findDateArchive();

        return $this->render(
            $this->getParagraphFile('post/archive'),
            [
                'archives'  => $archives,
                'paragraph' => $archive,
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