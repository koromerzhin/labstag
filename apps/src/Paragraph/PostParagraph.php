<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Post;
use Labstag\Entity\Post as EntityPost;
use Labstag\Form\Admin\Paragraph\PostType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;

class PostParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var PostRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(EntityPost::class);
        $posts         = $repositoryLib->getLimitOffsetResult(
            $repositoryLib->findPublier(),
            5,
            0
        );

        return [
            'posts'     => $posts,
            'paragraph' => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['post'];
    }

    public function getEntity(): string
    {
        return Post::class;
    }

    public function getForm(): string
    {
        return PostType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('post.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'post';
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
