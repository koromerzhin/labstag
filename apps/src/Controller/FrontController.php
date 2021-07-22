<?php

namespace Labstag\Controller;

use Labstag\Entity\Edito;
use Labstag\Lib\FrontControllerLib;
use Labstag\Repository\EditoRepository;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends FrontControllerLib
{
    /**
     * @Route("/edito", name="edito")
     */
    public function edito(EditoRepository $editoRepository): Response
    {
        /** @var Edito $edito */
        $edito = $editoRepository->findOnePublier();
        $this->setMetaOpenGraph(
            $edito->getTitle(),
            $edito->getMetaDescription(),
            $edito->getFond()
        );

        return $this->render(
            'front/edito.html.twig',
            ['edito' => $edito]
        );
    }

    /**
     * @Route("/", name="front")
     */
    public function index(EditoRepository $editoRepository, Request $request, PostRepository $postRepository): Response
    {
        $pagination = $this->paginator->paginate(
            $postRepository->findPublier(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            'front/index.html.twig',
            [
                'edito'      => $editoRepository->findOnePublier(),
                'pagination' => $pagination,
                'archives'   => $postRepository->findDateArchive(),
            ]
        );
    }
}
