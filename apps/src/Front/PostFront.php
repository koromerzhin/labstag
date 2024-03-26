<?php

namespace Labstag\Front;

use Exception;
use Labstag\Entity\Attachment;
use Labstag\Entity\Category;
use Labstag\Entity\Libelle;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Interfaces\FrontInterface;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\LibelleRepository;
use Symfony\Component\HttpFoundation\Request;

class PostFront extends PageFront implements FrontInterface
{
    public function setBreadcrumb(
        ?EntityFrontInterface $entityFront,
        array $breadcrumb
    ): array
    {
        $breadcrumb = $this->setBreadcrumbRouting($breadcrumb);
        if (!$entityFront instanceof Post) {
            return $breadcrumb;
        }

        $breadcrumb[] = [
            'route' => $this->router->generate(
                'front_article',
                [
                    'slug' => $entityFront->getSlug(),
                ]
            ),
            'title' => $entityFront->getTitle(),
        ];

        /** @var Page $page */
        $page = $this->pageRepository->findOneBy(
            ['slug' => 'mes-articles']
        );

        return $this->setBreadcrumbPage($page, $breadcrumb);
    }

    public function setMeta(
        ?EntityFrontInterface $entityFront,
        array $meta
    ): array
    {
        if (!$entityFront instanceof Post) {
            return $meta;
        }

        $meta = $this->getMeta($entityFront->getMetas(), $meta);
        if ($entityFront->getImg() instanceof Attachment) {
            $meta['image'] = $entityFront->getImg()->getName();
        }

        return $meta;
    }

    protected function setBreadcrumbRoutingCategory(
        string $route,
        array $params,
        array $breadcrumb
    ): array
    {
        if ('front_article_category' != $route) {
            return $breadcrumb;
        }

        /** @var CategoryRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Category::class);
        /** @var Category $category */
        $category = $repositoryLib->findOneBy(
            [
                'slug' => $params['slug'],
            ]
        );

        $breadcrumb[] = [
            'route' => $this->router->generate(
                $route,
                $params
            ),
            'title' => $category->getName(),
        ];
        /** @var Page $page */
        $page = $this->pageRepository->findOneBy(
            ['slug' => 'mes-articles']
        );

        return $this->setBreadcrumbPage($page, $breadcrumb);
    }

    protected function setBreadcrumbRoutingLibelle(
        string $route,
        array $params,
        array $breadcrumb
    ): array
    {
        if ('front_article_libelle' != $route) {
            return $breadcrumb;
        }

        /** @var LibelleRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Libelle::class);
        /** @var Libelle $libelle */
        $libelle = $repositoryLib->findOneBy(
            [
                'slug' => $params['slug'],
            ]
        );

        $breadcrumb[] = [
            'route' => $this->router->generate(
                $route,
                $params
            ),
            'title' => $libelle->getName(),
        ];
        /** @var Page $page */
        $page = $this->pageRepository->findOneBy(
            ['slug' => 'mes-articles']
        );

        return $this->setBreadcrumbPage($page, $breadcrumb);
    }

    protected function setBreadcrumbRoutingYear(
        string $route,
        array $params,
        array $breadcrumb
    ): array
    {
        if ('front_article_year' != $route && !isset($params['year'])) {
            return $breadcrumb;
        }

        $breadcrumb[] = [
            'route' => $this->router->generate(
                $route,
                $params
            ),
            'title' => $params['year'],
        ];
        /** @var Page $page */
        $page = $this->pageRepository->findOneBy(
            ['slug' => 'mes-articles/archive']
        );

        return $this->setBreadcrumbPage($page, $breadcrumb);
    }

    private function setBreadcrumbRouting(array $breadcrumb): array
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $all     = $request->attributes->all();
        $route   = $all['_route'];
        $params  = $all['_route_params'];

        $functions = [
            'setBreadcrumbRoutingYear',
            'setBreadcrumbRoutingLibelle',
            'setBreadcrumbRoutingCategory',
        ];
        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            $breadcrumb = call_user_func_array($callable, [$route, $params, $breadcrumb]);
            if (!is_array($breadcrumb)) {
                throw new Exception('breadcrumb must be an array');
            }
        }

        return $breadcrumb;
    }
}
