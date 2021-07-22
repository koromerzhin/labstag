<?php

namespace Labstag\Controller;

use Labstag\Entity\Post;
use Labstag\Lib\FrontControllerLib;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post")
 */
class PostController extends FrontControllerLib
{
    /**
     * @Route("/archive/{code}", name="post_archive")
     */
    public function archive(PostRepository $postRepository, Request $request, string $code)
    {
        $pagination = $this->paginator->paginate(
            $postRepository->findPublierArchive($code),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            'front/posts/list.html.twig',
            [
                'pagination' => $pagination,
                'archives'   => $postRepository->findDateArchive(),
            ]
        );
    }

    /**
     * @Route("/{slug}", name="post_show")
     */
    public function show(PostRepository $postRepository, Post $post)
    {
        $this->setMetaOpenGraph(
            $post->getTitle(),
            $post->getMetaDescription(),
            $post->getImg()
        );

        return $this->render(
            'front/posts/show.html.twig',
            [
                'post'     => $post,
                'archives' => $postRepository->findDateArchive(),
            ]
        );
    }

    /**
     * @Route("/user/{username}", name="post_user")
     */
    public function user(PostRepository $postRepository, Request $request, $username)
    {
        $pagination = $this->paginator->paginate(
            $postRepository->findPublierUsername($username),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            'front/posts/list.html.twig',
            [
                'pagination' => $pagination,
                'archives'   => $postRepository->findDateArchive(),
            ]
        );
    }
}
