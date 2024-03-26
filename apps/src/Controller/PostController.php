<?php

namespace Labstag\Controller;

use Labstag\Entity\Post;
use Labstag\Lib\FrontControllerLib;
use Labstag\Repository\PageRepository;
use Labstag\Repository\PostRepository;
use Labstag\Repository\RenderRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mes-articles', name: 'front_article')]
class PostController extends FrontControllerLib
{
    #[Route(
        path: '/{slug}',
        name: '',
        priority: 2,
        defaults: ['slug' => '']
    )]
    public function article(
        string $slug,
        PostRepository $postRepository,
        PageRepository $pageRepository
    ): Response
    {
        $post = $postRepository->findOneBy(
            ['slug' => $slug]
        );

        if (!$post instanceof Post) {
            if ('' != $slug) {
                return $this->page('mes-articles/'.$slug, $pageRepository);
            }

            return $this->page('mes-articles', $pageRepository);
        }

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $post]
        );
    }

    #[Route(
        path: '/categorie/{slug}',
        name: '_category',
        priority: 2
    )]
    public function category(
        string $slug,
        RenderRepository $renderRepository
    ): Response
    {
        unset($slug);
        $render = $renderRepository->findOneBy(
            ['url' => 'front_article_category']
        );

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $render]
        );
    }

    #[Route(
        path: '/libelle/{slug}',
        name: '_libelle',
        priority: 2
    )]
    public function libelle(
        string $slug,
        RenderRepository $renderRepository
    ): Response
    {
        unset($slug);
        $render = $renderRepository->findOneBy(
            ['url' => 'front_article_libelle']
        );

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $render]
        );
    }

    #[Route(
        path: '/user/{username}',
        name: '_user',
        priority: 2
    )]
    public function user(
        string $username,
        RenderRepository $renderRepository
    ): Response
    {
        unset($username);
        $render = $renderRepository->findOneBy(
            ['url' => 'front_article_user']
        );

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $render]
        );
    }

    #[Route(
        path: '/archive/{year}',
        name: '_year',
        priority: 2
    )]
    public function year(
        string $year,
        RenderRepository $renderRepository
    ): Response
    {
        unset($year);
        $render = $renderRepository->findOneBy(
            ['url' => 'front_article_year']
        );

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $render]
        );
    }
}
