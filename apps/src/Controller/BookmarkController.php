<?php

namespace Labstag\Controller;

use Labstag\Entity\Bookmark;
use Labstag\Lib\FrontControllerLib;
use Labstag\Repository\BookmarkRepository;
use Labstag\Repository\PageRepository;
use Labstag\Repository\RenderRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mes-liens', name: 'front_bookmark')]
class BookmarkController extends FrontControllerLib
{
    #[Route(
        path: '/{slug}',
        name: '',
        priority: 2,
        defaults: ['slug' => '']
    )]
    public function bookmark(
        string $slug,
        BookmarkRepository $bookmarkRepository,
        PageRepository $pageRepository
    ): Response|RedirectResponse
    {
        $bookmark = $bookmarkRepository->findOneBy(
            ['slug' => $slug]
        );

        if (!$bookmark instanceof Bookmark) {
            if ('' != $slug) {
                throw $this->createNotFoundException();
            }

            return $this->page('mes-liens', $pageRepository);
        }

        return new RedirectResponse((string) $bookmark->getUrl(), 302);
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
            ['url' => 'front_bookmark_category']
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
            ['url' => 'front_bookmark_libelle']
        );

        return $this->render(
            'skeleton/front.html.twig',
            ['content' => $render]
        );
    }
}
