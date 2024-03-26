<?php

namespace Labstag\Controller\Api;

use Exception;
use Labstag\Annotation\IgnoreSoftDelete;
use Labstag\Entity\Attachment;
use Labstag\Interfaces\EntityInterface;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Lib\ApiControllerLib;
use Labstag\Lib\RepositoryLib;
use Labstag\Service\RepositoryService;
use Labstag\Service\TrashService;
use Labstag\Service\WorkflowService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route(path: '/api/actions', name: 'api_action_')]
class ActionsController extends ApiControllerLib
{
    #[Route(path: '/delete/{entity}/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        string $entity,
        string $id
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];
        $entity = $this->getDataRestoreDelete($repositoryService, $entity, $id);
        if (is_null($entity) || !is_null($entity->getDeletedAt())) {
            $data['error'] = 'entité inconnu';

            return new JsonResponse($data);
        }

        $tokenValid = $this->tokenVerif(
            $csrfTokenManager,
            $request,
            'delete',
            $entity
        );
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $data['action'] = true;
        $this->deleteEntity($repositoryService, $entity);

        return new JsonResponse($data);
    }

    #[Route(path: '/deleties/{entity}/', name: 'deleties', methods: ['DELETE'])]
    public function deleties(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        string $entity,
        Request $request
    ): JsonResponse
    {
        return $this->deleteOrRestore(
            $repositoryService,
            $csrfTokenManager,
            $entity,
            $request,
            'deleties'
        );
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/destroies/{entity}', name: 'destroies', methods: ['DELETE'])]
    public function destroies(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        string $entity,
        Request $request
    ): JsonResponse
    {
        return $this->deleteOrRestore(
            $repositoryService,
            $csrfTokenManager,
            $entity,
            $request,
            'destroies'
        );
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/destroy/{entity}/{id}', name: 'destroy', methods: ['DELETE'])]
    public function destroy(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        string $entity,
        string $id
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];
        /** @var RepositoryLib $repository */
        $repository = $repositoryService->get($entity);
        /** @var EntityTrashInterface $entity */
        $entity = $repository->find($id);
        if (is_null($entity) || is_null($entity->getDeletedAt())) {
            $data['error'] = 'entité inconnu';

            return new JsonResponse($data);
        }

        $tokenValid = $this->tokenVerif(
            $csrfTokenManager,
            $request,
            'destroy',
            $entity
        );
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $data['action'] = true;
        $this->destroyEntity($repositoryService, $entity);

        return new JsonResponse($data);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/empties', name: 'empties', methods: ['DELETE'])]
    public function empties(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];
        $tokenValid = $this->tokenVerif($csrfTokenManager, $request, 'empties');
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $entities = explode(',', (string) $request->request->get('entities'));
        $error    = [];
        foreach ($entities as $entity) {
            /** @var RepositoryLib $repository */
            $repository = $repositoryService->get($entity);

            try {
                $this->deleteEntityByRepository($repository);
            } catch (Exception $exception) {
                $error[] = $exception->getMessage();
            }
        }

        $data['error'] = $error;
        if ([] === $error) {
            $data['action'] = true;
        }

        return new JsonResponse($data);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/empty/{entity}', name: 'empty', methods: ['DELETE'])]
    public function empty(
        RepositoryService $repositoryService,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        string $entity
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];
        /** @var RepositoryLib $repository */
        $repository = $repositoryService->get($entity);
        $tokenValid = $this->tokenVerif($csrfTokenManager, $request, 'empty');
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $error = [];

        try {
            $this->deleteEntityByRepository($repository);
        } catch (Exception $exception) {
            $error[] = $exception->getMessage();
        }

        $data['error'] = $error;
        if ([] === $error) {
            $data['action'] = true;
        }

        return new JsonResponse($data);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/emptyall', name: 'emptyall', methods: ['DELETE'])]
    public function emptyall(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        TrashService $trashService
    ): JsonResponse
    {
        $tokenValid = $this->tokenVerif($csrfTokenManager, $request, 'emptyall');
        $data       = [
            'action' => false,
            'error'  => '',
        ];
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $error         = $this->deleteAll($repositoryService, $trashService);
        $data['error'] = $error;
        if (0 === (is_countable($error) ? count($error) : 0)) {
            $data['action'] = true;
        }

        return new JsonResponse($data);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/restore/{entity}/{id}', name: 'restore', methods: ['POST'])]
    public function restore(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        string $entity,
        string $id
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];
        $entity = $this->getDataRestoreDelete($repositoryService, $entity, $id);
        if (is_null($entity) || is_null($entity->getDeletedAt())) {
            $data['error'] = 'entité inconnu';

            return new JsonResponse($data);
        }

        $tokenValid = $this->tokenVerif($csrfTokenManager, $request, 'restore', $entity);
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $data['action'] = true;
        $this->restoreEntity($repositoryService, $entity);

        return new JsonResponse($data);
    }

    #[IgnoreSoftDelete]
    #[Route(path: '/restories/{entity}', name: 'restories', methods: ['POST'])]
    public function restories(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        string $entity,
        Request $request
    ): JsonResponse
    {
        return $this->deleteOrRestore($repositoryService, $csrfTokenManager, $entity, $request, 'restories');
    }

    #[Route(path: '/workflow/{entity}/{state}/{id}', name: 'workflow', methods: ['POST'])]
    public function workflow(
        RepositoryService $repositoryService,
        WorkflowService $workflowService,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        string $entity,
        string $state,
        string $id
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];
        /** @var RepositoryLib $repository */
        $repository = $repositoryService->get($entity);
        $entity     = $repository->find($id);
        $this->denyAccessUnlessGranted('workflow-'.$state, $entity);
        if (is_null($entity) || !$entity instanceof EntityInterface) {
            $data['error'] = 'entité inconnu';

            return new JsonResponse($data);
        }

        $tokenValid = $this->tokenVerif($csrfTokenManager, $request, 'workflow-'.$state, $entity);
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $data['action'] = true;
        if ($workflowService->has($entity)) {
            /** @var WorkflowInterface $workflow */
            $workflow = $workflowService->get($entity);
            $workflow->apply($entity, $state);
            $repository->save($entity);
        }

        return new JsonResponse($data);
    }

    private function deleteAll(
        RepositoryService $repositoryService,
        TrashService $trashService
    ): array
    {
        $all   = $trashService->all();
        $error = [];
        foreach ($all as $data) {
            $entity = $data['name'];
            /** @var RepositoryLib $repository */
            $repository = $repositoryService->get($entity);

            try {
                $this->deleteEntityByRepository($repository);
            } catch (Exception $exception) {
                $error[] = $exception->getMessage();
            }
        }

        return $error;
    }

    private function deleteEntity(
        RepositoryService $repositoryService,
        ?EntityTrashInterface $entityTrash
    ): void
    {
        if (is_null($entityTrash) || !is_null($entityTrash->getDeletedAt())) {
            return;
        }

        /** @var RepositoryLib $repositoryLib */
        $repositoryLib = $repositoryService->get($entityTrash::class);
        $repositoryLib->remove($entityTrash);
    }

    private function deleteEntityByRepository(RepositoryLib $serviceEntityRepositoryLib): void
    {
        $queryBuilder = $serviceEntityRepositoryLib->findTrashForAdmin([]);
        $result       = $queryBuilder->getQuery()->getResult();
        $files        = [];
        if (!is_iterable($result)) {
            return;
        }

        foreach ($result as $entity) {
            $serviceEntityRepositoryLib->remove($entity);
            if (!$entity instanceof Attachment) {
                continue;
            }

            $files[] = $entity->getName();
        }

        if (0 == count($files)) {
            return;
        }

        foreach ($files as $file) {
            /** @var string $file */
            if ('' == $file && is_file($file)) {
                continue;
            }

            unlink($file);
        }
    }

    private function deleteOrRestore(
        RepositoryService $repositoryService,
        CsrfTokenManagerInterface $csrfTokenManager,
        string $entity,
        Request $request,
        string $token
    ): JsonResponse
    {
        $data = [
            'action' => false,
            'error'  => '',
        ];

        $tokenValid = $this->tokenVerif($csrfTokenManager, $request, $token);
        if (!$tokenValid) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $entities = explode(',', (string) $request->request->get('entities'));
        $error    = [];
        /** @var RepositoryLib $repository */
        $repository = $repositoryService->get($entity);
        $method     = match ($token) {
            'deleties'  => 'deleteEntity',
            'destroies' => 'destroyEntity',
            default     => 'restoreEntity'
        };

        foreach ($entities as $id) {
            try {
                $entity = $repository->find($id);
                /** @var callable $callable */
                $callable = [
                    $this,
                    $method,
                ];
                call_user_func($callable, $entity);
            } catch (Exception $exception) {
                $error[] = $exception->getMessage();
            }
        }

        $data['error'] = $error;
        if ([] === $error) {
            $data['action'] = true;
        }

        unset($entity);

        return new JsonResponse($data);
    }

    private function destroyEntity(
        RepositoryService $repositoryService,
        ?EntityTrashInterface $entityTrash
    ): void
    {
        if (is_null($entityTrash) || is_null($entityTrash->getDeletedAt())) {
            return;
        }

        $file = '';
        /** @var RepositoryLib $repository */
        $repository = $repositoryService->get($entityTrash::class);
        $repository->remove($entityTrash);
        if ($entityTrash instanceof Attachment) {
            $file = $entityTrash->getName();
        }

        /** @var string $file */
        if ('' != $file && is_file($file)) {
            unlink($file);
        }
    }

    private function getDataRestoreDelete(
        RepositoryService $repositoryService,
        string $entity,
        string $id
    ): ?EntityTrashInterface
    {
        /** @var RepositoryLib $repositoryLib */
        $repositoryLib = $repositoryService->get($entity);

        $data = $repositoryLib->find($id);
        if (!$data instanceof EntityTrashInterface) {
            return null;
        }

        return $data;
    }

    private function restoreEntity(
        RepositoryService $repositoryService,
        ?EntityTrashInterface $entityTrash
    ): void
    {
        if (is_null($entityTrash) || is_null($entityTrash->getDeletedAt())) {
            return;
        }

        $entityTrash->setDeletedAt(null);
        /** @var RepositoryLib $repositoryLib */
        $repositoryLib = $repositoryService->get($entityTrash::class);
        $repositoryLib->save($entityTrash);
    }

    private function tokenVerif(
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request,
        string $action,
        ?EntityInterface $entity = null
    ): bool
    {
        $token = $request->get('_token');
        if (!is_string($token)) {
            return false;
        }

        $csrfToken = new CsrfToken(
            $action.(is_null($entity) ? '' : $entity->getId()),
            $token
        );

        return $csrfTokenManager->isTokenValid($csrfToken);
    }
}
