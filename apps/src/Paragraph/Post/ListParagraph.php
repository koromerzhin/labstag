<?php

namespace Labstag\Paragraph\Post;

use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Post\Liste;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\Post\ListType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;

class ListParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var PostRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Post::class);
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $pagination = $this->paginator->paginate(
            $repositoryLib->findPublier(),
            $request->query->getInt('page', 1),
            10
        );

        return [
            'pagination' => $pagination,
            'paragraph'  => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['post/list'];
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
        return $this->translator->trans('postlist.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'postlist';
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
