<?php

namespace Labstag\Paragraph\Bookmark;

use Labstag\Entity\Bookmark;
use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Bookmark\Libelle;
use Labstag\Form\Admin\Paragraph\Bookmark\LibelleType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\BookmarkRepository;
use Symfony\Component\HttpFoundation\Request;

class LibelleParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $all        = $request->attributes->all();
        $routeParam = $all['_route_params'];
        $slug       = $routeParam['slug'] ?? null;
        /** @var BookmarkRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Bookmark::class);
        $pagination    = $this->paginator->paginate(
            $repositoryLib->findPublierLibelle($slug),
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

        return ['bookmark/libelle'];
    }

    public function getEntity(): string
    {
        return Libelle::class;
    }

    public function getForm(): string
    {
        return LibelleType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('bookmarklibelle.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'bookmarklibelle';
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
}
