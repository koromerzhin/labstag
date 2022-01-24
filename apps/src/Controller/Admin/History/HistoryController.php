<?php

namespace Labstag\Controller\Admin\History;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Chapter;
use Labstag\Entity\History;
use Labstag\Form\Admin\HistoryType;
use Labstag\Form\Admin\Search\HistoryType as SearchHistoryType;
use Labstag\Lib\AdminControllerLib;
use Labstag\RequestHandler\HistoryRequestHandler;
use Labstag\Search\HistorySearch;
use Labstag\Service\AttachFormService;
use Labstag\Service\HistoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/history")
 */
class HistoryController extends AdminControllerLib
{
    /**
     * @Route("/{id}/edit", name="admin_history_edit", methods={"GET","POST"})
     * @Route("/new", name="admin_history_new", methods={"GET","POST"})
     */
    public function edit(
        AttachFormService $service,
        ?History $history,
        HistoryRequestHandler $requestHandler
    ): Response
    {
        $this->modalAttachmentDelete();

        return $this->form(
            $service,
            $requestHandler,
            HistoryType::class,
            !is_null($history) ? $history : new History(),
            'admin/history/form.html.twig'
        );
    }

    /**
     * @Route("/trash", name="admin_history_trash", methods={"GET"})
     * @Route("/", name="admin_history_index", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function indexOrTrash(): Response
    {
        return $this->listOrTrash(
            History::class,
            'admin/history/index.html.twig',
        );
    }

    /**
     * @Route("/{id}/pdf", name="admin_history_pdf", methods={"GET"})
     */
    public function pdf(HistoryService $service, History $history)
    {
        $service->process(
            $this->getParameter('file_directory'),
            $history->getId(),
            true
        );

        $filename = $service->getFilename();
        if (empty($filename)) {
            throw $this->createNotFoundException('Pas de fichier');
        }

        $filename = str_replace(
            $this->getParameter('kernel.project_dir').'/public/',
            '/',
            $filename
        );

        return $this->redirect($filename);
    }

    /**
     * @Route("/{id}/move", name="admin_history_move", methods={"GET", "POST"})
     */
    public function position(
        History $history,
        Request $request,
        EntityManagerInterface $entityManager
    )
    {
        $currentUrl = $this->generateUrl(
            'admin_history_move',
            [
                'id' => $history->getId(),
            ]
        );

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all('position');
            if (!empty($data)) {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            }

            if (is_array($data)) {
                foreach ($data as $row) {
                    $id       = $row['id'];
                    $position = intval($row['position']);
                    $entity   = $this->getRepository(Chapter::class)->find($id);
                    if (!is_null($entity)) {
                        $entity->setPosition($position + 1);
                        $entityManager->persist($entity);
                    }
                }

                $entityManager->flush();
            }
        }

        $this->btnInstance()->addBtnList(
            'admin_history_index',
            'Liste',
        );

        $this->btnInstance()->add(
            'btn-admin-save-move',
            'Enregistrer',
            [
                'is'   => 'link-btnadminmove',
                'href' => $currentUrl,
            ]
        );

        return $this->render(
            'admin/history/move.html.twig',
            ['history' => $history]
        );
    }

    /**
     * @Route("/{id}", name="admin_history_show", methods={"GET"})
     * @Route("/preview/{id}", name="admin_history_preview", methods={"GET"})
     * @IgnoreSoftDelete
     */
    public function showOrPreview(
        History $history
    ): Response
    {
        return $this->renderShowOrPreview(
            $history,
            'admin/history/show.html.twig'
        );
    }

    protected function getUrlAdmin(): array
    {
        return [
            'delete'   => 'api_action_delete',
            'move'     => 'admin_history_move',
            'destroy'  => 'api_action_destroy',
            'edit'     => 'admin_history_edit',
            'empty'    => 'api_action_empty',
            'list'     => 'admin_history_index',
            'new'      => 'admin_history_new',
            'preview'  => 'admin_history_preview',
            'restore'  => 'api_action_restore',
            'show'     => 'admin_history_show',
            'trash'    => 'admin_history_trash',
            'workflow' => 'api_action_workflow',
        ];
    }

    protected function searchForm(): array
    {
        return [
            'form' => SearchHistoryType::class,
            'data' => new HistorySearch(),
        ];
    }

    protected function setBreadcrumbsPageAdminHistory(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.title', [], 'admin.breadcrumb'),
                'route' => 'admin_history_index',
            ],
        ];
    }

    protected function setBreadcrumbsPageAdminHistoryEdit(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.edit', [], 'admin.breadcrumb'),
                'route' => 'admin_history_edit',
            ],
        ];
    }

    protected function setBreadcrumbsPageAdminHistoryMove(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.move', [], 'admin.breadcrumb'),
                'route' => 'admin_history_move',
            ],
        ];
    }

    protected function setBreadcrumbsPageAdminHistoryNew(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.new', [], 'admin.breadcrumb'),
                'route' => 'admin_history_new',
            ],
        ];
    }

    protected function setBreadcrumbsPageAdminHistoryPreview(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.trash', [], 'admin.breadcrumb'),
                'route' => 'admin_history_trash',
            ],
            [
                'title' => $this->translator->trans('history.preview', [], 'admin.breadcrumb'),
                'route' => 'admin_history_preview',
            ],
        ];
    }

    protected function setBreadcrumbsPageAdminHistoryShow(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.show', [], 'admin.breadcrumb'),
                'route' => 'admin_history_show',
            ],
        ];
    }

    protected function setBreadcrumbsPageAdminHistoryTrash(): array
    {
        return [
            [
                'title' => $this->translator->trans('history.trash', [], 'admin.breadcrumb'),
                'route' => 'admin_history_trash',
            ],
        ];
    }

    protected function setHeaderTitle(): array
    {
        $headers = parent::setHeaderTitle();

        return array_merge(
            $headers,
            [
                'admin_history' => $this->translator->trans('history.title', [], 'admin.header'),
            ]
        );
    }
}
