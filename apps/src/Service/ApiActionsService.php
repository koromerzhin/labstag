<?php

namespace Labstag\Service;

use Labstag\Lib\ServiceEntityRepositoryLib;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ApiActionsService
{
    const REPOSITORY     = 'Repository';
    const REQUESTHANDLER = 'RequestHandler';

    protected ContainerInterface $container;

    protected CsrfTokenManagerInterface $csrfTokenManager;

    protected Request $request;

    protected RequestStack $requestStack;

    public function __construct(
        RequestStack $requestStack,
        CsrfTokenManagerInterface $csrfTokenManager,
        ContainerInterface $container
    )
    {
        $this->container        = $container;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->requestStack     = $requestStack;
        /** @var Request $request */
        $request       = $this->requestStack->getCurrentRequest();
        $this->request = $request;
    }

    public function getRepository(string $entity): ?ServiceEntityRepositoryLib
    {
        $repositories = $this->setRepository();

        foreach ($repositories as $repository) {
            $matches = [];
            preg_match(
                '#'.self::REPOSITORY.'\\\\'.$entity.self::REPOSITORY.'#i',
                $repository,
                $matches
            );

            if (0 != count($matches)) {
                return $this->container->get($repository);
            }
        }

        return null;
    }

    public function verifToken(string $action, $entity = null): bool
    {
        $token = $this->request->request->get('_token');

        $csrfToken = new CsrfToken(
            $action.(is_null($entity) ? '' : $entity->getId()),
            $token
        );

        return $this->csrfTokenManager->isTokenValid($csrfToken);
    }

    protected function setRepository(): array
    {
        $services     = $this->container->getServiceIds();
        $repositories = [];
        foreach ($services as $service) {
            $matches = [];
            preg_match(
                '/'.self::REPOSITORY.'/',
                $service,
                $matches
            );
            if (0 !== count($matches)) {
                $repositories[] = $service;
            }
        }

        return $repositories;
    }
}
