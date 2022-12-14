<?php

namespace Labstag\Domain;

use Labstag\Entity\Render;

use Labstag\Form\Admin\RenderType;
use Labstag\Form\Admin\Search\RenderType as SearchRenderType;
use Labstag\Lib\DomainLib;
use Labstag\Repository\RenderRepository;
use Labstag\RequestHandler\RenderRequestHandler;
use Labstag\Search\RenderSearch;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenderDomain extends DomainLib
{
    public function __construct(
        protected RenderRequestHandler $renderRequestHandler,
        protected RenderRepository $renderRepository,
        TranslatorInterface $translator
    )
    {
        parent::__construct($translator);
    }

    public function getEntity()
    {
        return Render::class;
    }

    public function getRepository()
    {
        return $this->renderRepository;
    }

    public function getRequestHandler()
    {
        return $this->renderRequestHandler;
    }

    public function getSearchData()
    {
        return new RenderSearch();
    }

    public function getSearchForm()
    {
        return SearchRenderType::class;
    }

    /**
     * @return mixed[]
     */
    public function getTitles(): array
    {
        return [
            'admin_render_index'   => $this->translator->trans('render.title', [], 'admin.breadcrumb'),
            'admin_render_edit'    => $this->translator->trans('render.edit', [], 'admin.breadcrumb'),
            'admin_render_new'     => $this->translator->trans('render.new', [], 'admin.breadcrumb'),
            'admin_render_trash'   => $this->translator->trans('render.trash', [], 'admin.breadcrumb'),
            'admin_render_preview' => $this->translator->trans('render.preview', [], 'admin.breadcrumb'),
            'admin_render_show'    => $this->translator->trans('render.show', [], 'admin.breadcrumb'),
        ];
    }

    public function getType()
    {
        return RenderType::class;
    }

    public function getUrlAdmin(): array
    {
        return [
            'delete'   => 'api_action_delete',
            'destroy'  => 'api_action_destroy',
            'edit'     => 'admin_render_edit',
            'empty'    => 'api_action_empty',
            'list'     => 'admin_render_index',
            'new'      => 'admin_render_new',
            'preview'  => 'admin_render_preview',
            'restore'  => 'api_action_restore',
            'show'     => 'admin_render_show',
            'trash'    => 'admin_render_trash',
            'workflow' => 'api_action_workflow',
        ];
    }
}
