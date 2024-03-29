<?php

namespace Labstag\Domain;

use Labstag\Entity\Layout;
use Labstag\Form\Admin\LayoutType;

use Labstag\Form\Admin\Search\LayoutType as SearchLayoutType;
use Labstag\Interfaces\DomainInterface;
use Labstag\Lib\DomainLib;
use Labstag\Search\LayoutSearch;

class LayoutDomain extends DomainLib implements DomainInterface
{
    public function getEntity(): string
    {
        return Layout::class;
    }

    public function getSearchData(): LayoutSearch
    {
        return new LayoutSearch();
    }

    public function getSearchForm(): string
    {
        return SearchLayoutType::class;
    }

    public function getTemplates(): array
    {
        return [
            'index'   => 'admin/layout/index.html.twig',
            'trash'   => 'admin/layout/index.html.twig',
            'edit'    => 'admin/layout/form.html.twig',
            'show'    => 'admin/layout/show.html.twig',
            'preview' => 'admin/layout/show.html.twig',
        ];
    }

    public function getTitles(): array
    {
        return [
            'admin_layout_index'   => $this->translator->trans('layout.title', [], 'admin.breadcrumb'),
            'admin_layout_edit'    => $this->translator->trans('layout.edit', [], 'admin.breadcrumb'),
            'admin_layout_new'     => $this->translator->trans('layout.new', [], 'admin.breadcrumb'),
            'admin_layout_trash'   => $this->translator->trans('layout.trash', [], 'admin.breadcrumb'),
            'admin_layout_preview' => $this->translator->trans('layout.preview', [], 'admin.breadcrumb'),
            'admin_layout_show'    => $this->translator->trans('layout.show', [], 'admin.breadcrumb'),
        ];
    }

    public function getType(): string
    {
        return LayoutType::class;
    }

    public function getUrlAdmin(): array
    {
        return [
            'delete'   => 'api_action_delete',
            'destroy'  => 'api_action_destroy',
            'edit'     => 'admin_layout_edit',
            'empty'    => 'api_action_empty',
            'list'     => 'admin_layout_index',
            'add'      => 'admin_layout_new',
            'preview'  => 'admin_layout_preview',
            'restore'  => 'api_action_restore',
            'show'     => 'admin_layout_show',
            'trash'    => 'admin_layout_trash',
            'workflow' => 'api_action_workflow',
        ];
    }
}
