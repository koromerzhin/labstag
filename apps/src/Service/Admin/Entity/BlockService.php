<?php

namespace Labstag\Service\Admin\Entity;

use Exception;
use Labstag\Entity\Block;
use Labstag\Form\Admin\NewBlockType;
use Labstag\Interfaces\AdminEntityServiceInterface;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\BlockRepository;
use Labstag\Service\Admin\ViewService;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class BlockService extends ViewService implements AdminEntityServiceInterface
{
    public function edit(
        EntityInterface $entity,
        array $parameters = []
    ): Response
    {
        if (!$entity instanceof Block) {
            throw new Exception('Entity not found');
        }

        $field      = $this->blockService->getEntityField($entity);
        $parameters = [
            ...$parameters,
            'field' => $field,
        ];

        return parent::edit($entity, $parameters);
    }

    public function getType(): string
    {
        return Block::class;
    }

    public function index(
        array $parameters = []
    ): Response
    {
        return $this->listOrTrash('index', $parameters);
    }

    public function move(): Response
    {
        $routes = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['move']) || !isset($routes['list'])) {
            throw new Exception('Route edit not found');
        }

        $currentUrl = $this->generateUrl($routes['move']);
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        if ('POST' == $request->getMethod()) {
            $this->setPositionEntity($request, Block::class);
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

        /** @var BlockRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Block::class);
        $data          = $repositoryLib->getDataByRegion(null);

        $templates = $this->getDomain()->getTemplates();
        if (!isset($templates['move'])) {
            throw new Exception('Template move not found');
        }

        return $this->render(
            $templates['move'],
            ['data' => $data]
        );
    }

    public function new(
        array $parameters = []
    ): RedirectResponse
    {
        unset($parameters);
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $post    = $request->request->all('new_block');
        $block   = new Block();
        if (!is_string($post['region']) || !is_string($post['type'])) {
            throw new Exception('Region or type not found');
        }

        $block->setTitle(Uuid::v1());
        $block->setRegion($post['region']);
        $block->setType($post['type']);

        /** @var BlockRepository $blockRepository */
        $blockRepository = $this->repositoryService->get(Block::class);

        $blockRepository->save($block);

        $routes = $this->getDomain()->getUrlAdmin();
        if (!isset($routes['edit'])) {
            throw new Exception('Route edit not found');
        }

        return $this->redirectToRoute($routes['edit'], ['id' => $block->getId()]);
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
        $region = null;
        $routes = $domain->getUrlAdmin();
        /** @var Request $request */
        $request   = $this->requeststack->getCurrentRequest();
        $all       = $request->attributes->all();
        $route     = $all['_route'];
        $routeType = (0 != substr_count((string) $route, 'trash')) ? 'trash' : 'all';
        $templates = $domain->getTemplates();
        if (!array_key_exists($type, $templates) || !isset($routes['add']) || !isset($routes['move'])) {
            throw new Exception('Route add or move not found');
        }

        $this->btnService->add(
            'btn-admin-header-new',
            'Nouveau',
            [
                'is'       => 'link-btnadminnewblock',
                'data-url' => $this->router->generate($routes['add']),
            ]
        );
        $this->btnService->addBtnList(
            $routes['move'],
            'Move',
        );
        $form = $this->createForm(
            NewBlockType::class,
            new Block(),
            [
                'action' => $this->router->generate($routes['add']),
            ]
        );

        $url = $domain->getUrlAdmin();
        /** @var BlockRepository $serviceEntityRepositoryLib */
        $serviceEntityRepositoryLib = $domain->getRepository();
        $this->btnService->setBtnListOrTrash($domain, $routeType);
        $data  = $serviceEntityRepositoryLib->getDataByRegion(null);
        $total = 0;
        foreach ($data as $region) {
            $total += is_countable($region) ? count($region) : 0;
        }

        if (('trash' == $routeType && 0 == $total)) {
            throw new AccessDeniedException();
        }

        $parameters = [
            ...$parameters,
            'data'    => $data,
            'actions' => $url,
            'newform' => $form,
        ];

        return $this->render(
            $templates[$type],
            $parameters
        );
    }
}
