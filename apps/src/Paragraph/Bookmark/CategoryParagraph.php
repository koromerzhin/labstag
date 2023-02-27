<?php

namespace Labstag\Paragraph\Bookmark;

use Labstag\Entity\Bookmark;
use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Bookmark\Category;
use Labstag\Form\Admin\Paragraph\Bookmark\CategoryType;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\BookmarkRepository;
use Symfony\Component\HttpFoundation\Response;

class CategoryParagraph extends ParagraphLib
{
    public function getCode(ParagraphInterface $entityParagraphLib): string
    {
        unset($entityParagraphLib);

        return 'bookmark/category';
    }

    public function getEntity(): string
    {
        return Category::class;
    }

    public function getForm(): string
    {
        return CategoryType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('bookmarkcategory.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'bookmarkcategory';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function show(Category $category): Response
    {
        $all = $this->request->attributes->all();
        $routeParam = $all['_route_params'];
        $slug = $routeParam['slug'] ?? null;
        /** @var BookmarkRepository $entityRepository */
        $entityRepository = $this->getRepository(Bookmark::class);
        $pagination = $this->paginator->paginate(
            $entityRepository->findPublierCategory($slug),
            $this->request->query->getInt('page', 1),
            10
        );

        return $this->render(
            $this->getTemplateFile($this->getCode($category)),
            [
                'pagination' => $pagination,
                'paragraph'  => $category,
            ]
        );
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
