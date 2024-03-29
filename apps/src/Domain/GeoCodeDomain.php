<?php

namespace Labstag\Domain;

use Labstag\Entity\GeoCode;
use Labstag\Form\Admin\GeoCodeType;

use Labstag\Form\Admin\Search\GeoCodeType as SearchGeoCodeType;
use Labstag\Interfaces\DomainInterface;
use Labstag\Lib\DomainLib;
use Labstag\Search\GeoCodeSearch;

class GeoCodeDomain extends DomainLib implements DomainInterface
{
    public function getEntity(): string
    {
        return GeoCode::class;
    }

    public function getSearchData(): GeoCodeSearch
    {
        return new GeoCodeSearch();
    }

    public function getSearchForm(): string
    {
        return SearchGeoCodeType::class;
    }

    public function getTemplates(): array
    {
        return [
            'index'   => 'admin/geocode/index.html.twig',
            'trash'   => 'admin/geocode/index.html.twig',
            'show'    => 'admin/geocode/show.html.twig',
            'preview' => 'admin/geocode/show.html.twig',
        ];
    }

    public function getTitles(): array
    {
        return [
            'admin_geocode_index'   => $this->translator->trans('geocode.title', [], 'admin.breadcrumb'),
            'admin_geocode_edit'    => $this->translator->trans('geocode.edit', [], 'admin.breadcrumb'),
            'admin_geocode_new'     => $this->translator->trans('geocode.new', [], 'admin.breadcrumb'),
            'admin_geocode_trash'   => $this->translator->trans('geocode.trash', [], 'admin.breadcrumb'),
            'admin_geocode_preview' => $this->translator->trans('geocode.preview', [], 'admin.breadcrumb'),
            'admin_geocode_show'    => $this->translator->trans('geocode.show', [], 'admin.breadcrumb'),
        ];
    }

    public function getType(): string
    {
        return GeoCodeType::class;
    }

    public function getUrlAdmin(): array
    {
        return [
            'delete'      => 'api_action_delete',
            'destroy'     => 'api_action_destroy',
            'edit'        => 'admin_geocode_edit',
            'empty'       => 'api_action_empty',
            'list'        => 'admin_geocode_index',
            'new'         => 'admin_geocode_new',
            'restore'     => 'api_action_restore',
            'show'        => 'admin_geocode_show',
            'trash'       => 'admin_geocode_trash',
            'trashdelete' => 'admin_geocode_destroy',
        ];
    }
}
