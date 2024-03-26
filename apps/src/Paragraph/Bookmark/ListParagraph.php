<?php

namespace Labstag\Paragraph\Bookmark;

use Labstag\Entity\Bookmark;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Bookmark\Liste;
use Labstag\Form\Admin\Paragraph\Bookmark\ListType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\BookmarkRepository;
use Symfony\Component\HttpFoundation\Request;

class ListParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var BookmarkRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Bookmark::class);
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

        return ['bookmark/list'];
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
        return $this->translator->trans('bookmarklist.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'bookmarklist';
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
