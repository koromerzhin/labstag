<?php

namespace Labstag\Lib;

use Knp\Component\Pager\PaginatorInterface;
use Labstag\Service\DataService;
use Labstag\Singleton\BreadcrumbsSingleton;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

abstract class ControllerLib extends AbstractController
{

    protected Breadcrumbs $breadcrumbs;

    protected BreadcrumbsSingleton $breadcrumbsInstance;

    protected DataService $dataService;

    protected PaginatorInterface $paginator;

    public function __construct(
        DataService $dataService,
        Breadcrumbs $breadcrumbs,
        PaginatorInterface $paginator
    )
    {
        $this->dataService = $dataService;
        $this->breadcrumbs = $breadcrumbs;
        $this->paginator   = $paginator;
        $this->setSingletons();
    }

    public function render(
        string $view,
        array $parameters = [],
        ?Response $response = null
    ): Response
    {
        $this->setBreadcrumbs();
        if (isset($this->headerTitle) && '' != $this->headerTitle) {
            $parameters['headerTitle'] = $this->headerTitle;
        }

        return parent::render($view, $parameters, $response);
    }

    protected function setBreadcrumbs(): void
    {
        $data = $this->breadcrumbsInstance->get();
        foreach ($data as $title => $route) {
            $this->breadcrumbs->addItem($title, $route);
        }
    }

    protected function setSingletons()
    {
        $this->breadcrumbsInstance = BreadcrumbsSingleton::getInstance();
    }
}
