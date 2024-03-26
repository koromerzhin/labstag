<?php

namespace Labstag\Lib;

use Exception;
use Labstag\Service\RepositoryService;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class DomainLib
{
    public function __construct(
        protected RepositoryService $repositoryService,
        protected TranslatorInterface $translator
    )
    {
    }

    public function getEntity(): string
    {
        return '';
    }

    public function getMethodsList(): array
    {
        return [
            'trash' => 'findTrashForAdmin',
            'all'   => 'findAllForAdmin',
        ];
    }

    public function getRepository(): RepositoryLib
    {
        $entity = $this->getEntity();
        if ('' === $entity) {
            throw new Exception('Entity is empty');
        }

        $repositoryLib = $this->repositoryService->get($entity);
        if (!$repositoryLib instanceof RepositoryLib) {
            throw new Exception('Repository not found');
        }

        return $repositoryLib;
    }

    public function getSearchForm(): string
    {
        return '';
    }

    public function getUrlAdmin(): array
    {
        return [];
    }
}
