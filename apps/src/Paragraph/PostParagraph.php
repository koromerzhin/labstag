<?php

namespace Labstag\Paragraph;

use Symfony\Component\HttpFoundation\Response;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Post;
use Labstag\Entity\Post as EntityPost;
use Labstag\Form\Admin\Paragraph\PostType;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;

class PostParagraph extends ParagraphLib
{
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

    public function show(Post $post): Response
    {
        /** @var PostRepository $repository */
        $repository = $this->getRepository(EntityPost::class);
        $posts      = $repository->getLimitOffsetResult($repository->findPublier(), 5, 0);

        return $this->render(
            $this->getParagraphFile('post'),
            [
                'posts'     => $posts,
                'paragraph' => $post,
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
