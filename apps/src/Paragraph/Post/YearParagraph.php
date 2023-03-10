<?php

namespace Labstag\Paragraph\Post;

use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Post\Year;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\Post\YearType;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YearParagraph extends ParagraphLib
{
    public function getCode(ParagraphInterface $entityParagraphLib): string
    {
        unset($entityParagraphLib);

        return 'post/year';
    }

    public function getEntity(): string
    {
        return Year::class;
    }

    public function getForm(): string
    {
        return YearType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('postyear.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'postyear';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function show(Year $postyear): Response
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $all        = $request->attributes->all();
        $routeParam = $all['_route_params'];
        $year       = $routeParam['year'] ?? null;
        /** @var PostRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $this->repositoryService->get(Post::class);
        $pagination                 = $this->paginator->paginate(
            $serviceEntityRepositoryLib->findPublierArchive($year),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            $this->getTemplateFile($this->getCode($postyear)),
            [
                'pagination' => $pagination,
                'paragraph'  => $postyear,
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
