<?php

namespace Labstag\Controller\Api;

use Labstag\Entity\Category;
use Labstag\Entity\Groupe;
use Labstag\Entity\Libelle;
use Labstag\Entity\User;
use Labstag\Lib\ApiControllerLib;
use Labstag\Lib\RepositoryLib;
use Labstag\Service\RepositoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/search', name: 'api_search_')]
class SearchController extends ApiControllerLib
{
    #[Route(path: '/category', name: 'category')]
    #[Route(path: '/group', name: 'group')]
    #[Route(path: '/libelle', name: 'postlibelle')]
    #[Route(path: '/user', name: 'user')]
    public function libelle(
        RepositoryService $repositoryService,
        Request $request
    ): JsonResponse
    {
        $attributes = $request->attributes->all();
        $route      = $attributes['_route'];
        $entityName = match ($route) {
            'api_search_user'        => User::class,
            'api_search_category'    => Category::class,
            'api_search_group'       => Groupe::class,
            'api_search_postlibelle' => Libelle::class,
            default                  => null
        };

        $function = match ($route) {
            'api_search_user' => 'findUserName',
            default           => 'findName'
        };

        return $this->showData($repositoryService, $request, $entityName, $function);
    }

    private function showData(
        RepositoryService $repositoryService,
        Request $request,
        ?string $entity,
        string $method
    ): JsonResponse
    {
        $get    = $request->query->all();
        $return = ['isvalid' => false];
        if (!array_key_exists('name', $get) || is_null($entity)) {
            return $this->json($return);
        }

        $repositoryLib = $repositoryService->get($entity);
        if (!$repositoryLib instanceof RepositoryLib) {
            return $this->json($return);
        }

        $return = [
            'results' => [],
        ];
        /** @var callable $callable */
        $callable = [
            $repositoryLib,
            $method,
        ];
        $data = call_user_func($callable, $get['name']);
        if (!is_iterable($data)) {
            return $this->json($return);
        }

        foreach ($data as $user) {
            $return['results'][] = [
                'id'   => $user->getId(),
                'text' => (string) $user,
            ];
        }

        return $this->json($return);
    }
}
