<?php

namespace Labstag\Service\Admin\Entity;

use Exception;
use Labstag\Entity\Block\Custom;
use Labstag\Entity\Layout;
use Labstag\Form\Admin\NewLayoutType;
use Labstag\Interfaces\AdminEntityServiceInterface;
use Labstag\Interfaces\DomainInterface;
use Labstag\Repository\Block\CustomRepository;
use Labstag\Repository\LayoutRepository;
use Labstag\Service\Admin\ViewService;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class LayoutService extends ViewService implements AdminEntityServiceInterface
{
    public function getType(): string
    {
        return Layout::class;
    }

    public function index(
        array $parameters = []
    ): Response
    {
        return $this->listOrTrash('index', $parameters);
    }

    public function new(
        array $parameters = []
    ): RedirectResponse
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        unset($parameters);
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $post    = $request->request->all('new_layout');

        $routes = $domain->getUrlAdmin();
        if (!isset($routes['list']) || !isset($routes['edit'])) {
            throw new Exception('Route list not found');
        }

        /** @var CustomRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Custom::class);
        $custom        = $repositoryLib->findOneBy(
            [
                'id' => $post['custom'],
            ]
        );
        if (!$custom instanceof Custom) {
            return $this->redirectToRoute($routes['list']);
        }

        $layout = new Layout();
        $layout->setCustom($custom);
        $layout->setName(Uuid::v1());

        /** @var LayoutRepository $layoutRepository */
        $layoutRepository = $this->repositoryService->get(Layout::class);

        $layoutRepository->save($layout);

        return $this->redirectToRoute($routes['edit'], ['id' => $layout->getId()]);
    }

    public function trash(
        array $parameters = []
    ): Response
    {
        return $this->listOrTrash('trash', $parameters);
    }

    private function listOrTrash(
        string $type,
        array $parameters = []
    ): Response
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $routes = $domain->getUrlAdmin();
        if (!isset($routes['add'])) {
            throw new Exception('Route add not found');
        }

        $this->btnService->add(
            'btn-admin-header-new',
            'Nouveau',
            [
                'is'       => 'link-btnadminnewblock',
                'data-url' => $this->router->generate($routes['add']),
            ]
        );
        $form = $this->createForm(
            NewLayoutType::class,
            new Layout(),
            [
                'action' => $this->router->generate($routes['add']),
            ]
        );

        $url = $domain->getUrlAdmin();
        /** @var Request $request */
        $request   = $this->requeststack->getCurrentRequest();
        $all       = $request->attributes->all();
        $route     = $all['_route'];
        $routeType = (0 != substr_count((string) $route, 'trash')) ? 'trash' : 'all';
        $this->btnService->setBtnListOrTrash($domain, $routeType);
        $pagination = $this->setPagination($routeType);

        if ('trash' == $routeType && 0 == $pagination->count()) {
            throw new AccessDeniedException();
        }

        $parameters = [
            'newform'    => $form,
            'pagination' => $pagination,
            'actions'    => $url,
        ];
        $parameters = $this->setSearchForms($parameters);

        $templates = $domain->getTemplates();

        if (!array_key_exists($type, $templates)) {
            throw new Exception('Template not found');
        }

        return $this->render(
            $templates[$type],
            $parameters
        );
    }
}
