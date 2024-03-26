<?php

namespace Labstag\Lib;

use Labstag\Entity\Route;
use Labstag\Entity\RouteUser;
use Labstag\Entity\User;
use Labstag\Entity\Workflow;
use Labstag\Entity\WorkflowGroupe;
use Labstag\Repository\UserRepository;
use Labstag\Service\RepositoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class ApiControllerLib extends AbstractController
{
    protected function getGuardRouteOrWorkflow(
        RepositoryService $repositoryService,
        UserRepository $userRepository,
        array $data,
        array $get,
        string $entityClass
    ): array
    {
        if (!array_key_exists('user', $get)) {
            return $data;
        }

        $data['user'] = [];
        $user         = $userRepository->find($get['user']);
        if (!$user instanceof User) {
            return $data;
        }

        /** @var RepositoryLib $entityRepository */
        $entityRepository = $repositoryService->get($entityClass);
        $results          = $entityRepository->findEnableByUser($user);
        if (RouteUser::class == $entityClass) {
            if (!is_iterable($results)) {
                return $data;
            }

            foreach ($results as $result) {
                /** @var RouteUser $result */
                /** @var Route $route */
                $route          = $result->getRefroute();
                $data['user'][] = [
                    'route' => $route->getName(),
                ];
            }

            return $data;
        }

        if (!is_iterable($results)) {
            return $data;
        }

        foreach ($results as $result) {
            /** @var WorkflowGroupe $result */
            /** @var Workflow $workflow */
            $workflow        = $result->getRefworkflow();
            $data['group'][] = [
                'entity'     => $workflow->getEntity(),
                'transition' => $workflow->getTransition(),
            ];
        }

        return $data;
    }

    protected function getResultWorkflow(
        RepositoryService $repositoryService,
        Request $request,
        string $entity
    ): mixed
    {
        /** @var RepositoryLib $repositoryLib */
        $repositoryLib = $repositoryService->get(User::class);
        /** @var RepositoryLib $entityRepository */
        $entityRepository = $repositoryService->get($entity);
        $get              = $request->query->all();
        if (array_key_exists('user', $get)) {
            /** @var User $user */
            $user = $repositoryLib->find($get['user']);

            return $entityRepository->findEnableByGroupe($user->getRefgroupe());
        }

        return $entityRepository->findEnableByGroupe();
    }
}
