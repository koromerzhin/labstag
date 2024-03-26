<?php

namespace Labstag\Service\Admin\Entity;

use DateTime;
use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Interfaces\AdminEntityServiceInterface;
use Labstag\Repository\HistoryRepository;
use Labstag\Service\Admin\ViewService;
use Labstag\Service\HistoryService as ServiceHistoryService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class HistoryService extends ViewService implements AdminEntityServiceInterface
{
    public function add(
        Security $security
    ): RedirectResponse
    {
        $routes = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['list']) || !isset($routes['edit'])) {
            throw new Exception('Route not found');
        }

        $user = $security->getUser();
        if (is_null($user)) {
            return $this->redirectToRoute($routes['list']);
        }

        $history = new History();
        $history->setPublished(new DateTime());
        $history->setName(Uuid::v1());
        $history->setRefuser($user);

        /** @var HistoryRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(History::class);
        $repositoryLib->save($history);

        return $this->redirectToRoute($routes['edit'], ['id' => $history->getId()]);
    }

    public function getType(): string
    {
        return History::class;
    }

    public function pdf(
        ServiceHistoryService $serviceHistoryService,
        History $history
    ): RedirectResponse
    {
        $fileDirectory    = $this->getParameter('file_directory');
        $kernelProjectDir = $this->getParameter('kernel.project_dir');
        if (!is_string($fileDirectory) || !is_string($kernelProjectDir)) {
            throw $this->createNotFoundException('Pas de fichier');
        }

        $serviceHistoryService->process(
            (string) $fileDirectory,
            (string) $history->getId(),
            true
        );
        $filename = $serviceHistoryService->getFilename();
        if (null === $filename || '' === $filename) {
            throw $this->createNotFoundException('Pas de fichier');
        }

        $filename = str_replace(
            ((string) $kernelProjectDir).'/public/',
            '/',
            $filename
        );

        return $this->redirect($filename);
    }

    public function position(History $history): Response
    {
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $routes  = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['list']) || !isset($routes['edit']) || !isset($routes['move'])) {
            throw new Exception('Route not found');
        }

        $currentUrl = $this->generateUrl(
            $routes['move'],
            [
                'id' => $history->getId(),
            ]
        );
        if ('POST' == $request->getMethod()) {
            $this->setPositionEntity($request, Chapter::class);
        }

        $this->btnService->addBtnList(
            $routes['list'],
            'Liste',
        );
        $this->btnService->add(
            'btn-admin-save-move',
            'Enregistrer',
            [
                'is'   => 'link-btnadminmove',
                'href' => $currentUrl,
            ]
        );
        $templates = $this->getDomain()->getTemplates();
        if (!isset($templates['move'])) {
            throw new Exception('Template not found');
        }

        return $this->render(
            $templates['move'],
            ['history' => $history]
        );
    }
}
