<?php

namespace Labstag\Controller\Admin;

use Knp\Component\Pager\PaginatorInterface;
use Labstag\Entity\Configuration;
use Labstag\Form\Admin\ConfigurationType;
use Labstag\Repository\ConfigurationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/configuration")
 */
class ConfigurationController extends AbstractController
{
    /**
     * @Route("/", name="configuration_index", methods={"GET"})
     */
    public function index(
        PaginatorInterface $paginator,
        Request $request,
        ConfigurationRepository $repository
    ): Response
    {
        $pagination = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1), /*page number*/
            10
        );
        return $this->render(
            'admin/configuration/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * @Route("/new", name="configuration_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $configuration = new Configuration();
        $form          = $this->createForm(
            ConfigurationType::class,
            $configuration
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($configuration);
            $entityManager->flush();

            return $this->redirectToRoute('configuration_index');
        }

        return $this->render(
            'admin/configuration/new.html.twig',
            [
                'configuration' => $configuration,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="configuration_show", methods={"GET"})
     */
    public function show(Configuration $configuration): Response
    {
        return $this->render(
            'admin/configuration/show.html.twig',
            ['configuration' => $configuration]
        );
    }

    /**
     * @Route("/{id}/edit", name="configuration_edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        Configuration $configuration
    ): Response {
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('configuration_index');
        }

        return $this->render(
            'admin/configuration/edit.html.twig',
            [
                'configuration' => $configuration,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="configuration_delete", methods={"DELETE"})
     */
    public function delete(
        Request $request,
        Configuration $configuration
    ): Response {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$configuration->getId(), $token)) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($configuration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('configuration_index');
    }
}
