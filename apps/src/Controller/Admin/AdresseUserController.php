<?php

namespace Labstag\Controller\Admin;

use Knp\Component\Pager\PaginatorInterface;
use Labstag\Entity\AdresseUser;
use Labstag\Form\Admin\AdresseUserType;
use Labstag\Repository\AdresseUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user/adresse")
 */
class AdresseUserController extends AbstractController
{
    /**
     * @Route("/", name="adresse_user_index", methods={"GET"})
     */
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        AdresseUserRepository $repository
    ): Response {
        $pagination = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1), /*page number*/
            10
        );
        return $this->render(
            'admin/adresse_user/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * @Route("/new", name="adresse_user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $adresseUser = new AdresseUser();
        $form        = $this->createForm(AdresseUserType::class, $adresseUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($adresseUser);
            $entityManager->flush();

            return $this->redirectToRoute('adresse_user_index');
        }

        return $this->render(
            'admin/adresse_user/new.html.twig',
            [
                'adresse_user' => $adresseUser,
                'form'         => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="adresse_user_show", methods={"GET"})
     */
    public function show(AdresseUser $adresseUser): Response
    {
        return $this->render(
            'admin/adresse_user/show.html.twig',
            ['adresse_user' => $adresseUser]
        );
    }

    /**
     * @Route("/{id}/edit", name="adresse_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, AdresseUser $adresseUser): Response
    {
        $form = $this->createForm(AdresseUserType::class, $adresseUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adresse_user_index');
        }

        return $this->render(
            'admin/adresse_user/edit.html.twig',
            [
                'adresse_user' => $adresseUser,
                'form'         => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="adresse_user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, AdresseUser $adresseUser): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$adresseUser->getId(), $token)) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($adresseUser);
            $entityManager->flush();
        }

        return $this->redirectToRoute('adresse_user_index');
    }
}
