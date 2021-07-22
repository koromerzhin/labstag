<?php

namespace Labstag\Controller\Api;

use Labstag\Entity\Groupe;
use Labstag\Entity\RouteGroupe;
use Labstag\Entity\RouteUser;
use Labstag\Entity\User;
use Labstag\Lib\ApiControllerLib;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\RouteGroupeRepository;
use Labstag\Repository\RouteUserRepository;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\RouteGroupeRequestHandler;
use Labstag\RequestHandler\RouteUserRequestHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @Route("/api/guard")
 */
class GuardController extends ApiControllerLib
{
    /**
     * @Route("/groups/{groupe}", name="api_guard_group")
     *
     * @return Response
     */
    public function groupe(Groupe $groupe, RouteGroupeRepository $routeGroupeRepo): JsonResponse
    {
        return $this->getRefgroupe($routeGroupeRepo, $groupe);
    }

    /**
     * @Route("/groups", name="api_guard_groups")
     *
     * @return Response
     */
    public function groupes(RouteGroupeRepository $routeGroupeRepo): JsonResponse
    {
        return $this->getRefgroupe($routeGroupeRepo);
    }

    /**
     * @Route("/setgroup/{route}/{groupe}", name="api_guard_setgroup")
     *
     * @return Response
     */
    public function setgroup(
        string $route,
        string $groupe,
        GroupeRepository $groupeRepo,
        RouteGroupeRequestHandler $routeGroupeRH,
        RouteGroupeRepository $routeGroupeRepo
    ): JsonResponse
    {
        $post   = $this->request->request->all();
        $data   = ['ok' => false];
        $groupe = $groupeRepo->findOneBy(['code' => $groupe]);
        $route  = $this->routeRepo->findOneBy(['name' => $route]);
        if (empty($groupe) || empty($route) || !array_key_exists('_token', $post)) {
            $data['error'] = 'Erreur de saisie';

            return new JsonResponse($data);
        }

        $groupeId  = $groupe->getId();
        $routeId   = $route->getId();
        $csrfToken = new CsrfToken(
            'guard-'.$groupeId.'-route-'.$routeId,
            $post['_token']
        );
        if (!is_null($csrfToken) && $this->csrfTokenManager->isTokenValid($csrfToken)) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $routeGroupe = $routeGroupeRepo->findOneBy(
            [
                'refgroupe' => $groupe,
                'refroute'  => $route,
            ]
        );
        if (!$routeGroupe instanceof RouteGroupe) {
            $routeGroupe = new RouteGroupe();
            $routeGroupe->setRefGroupe($groupe);
            $routeGroupe->setRefRoute($route);
        }

        $old = clone $routeGroupe;
        $routeGroupe->setState($post['state']);
        $routeGroupeRH->handle($old, $routeGroupe);
        $data['ok'] = true;

        return new JsonResponse($data);
    }

    /**
     * @Route("/setuser/{route}/{user}", name="api_guard_setuser", methods={"POST"})
     *
     * @return Response
     */
    public function setuser(
        string $route,
        string $user,
        UserRepository $userRepo,
        RouteUserRepository $routeUserRepo,
        RouteUserRequestHandler $routeUserRH
    ): JsonResponse
    {
        $data  = ['ok' => false];
        $post  = $this->request->request->all();
        $user  = $userRepo->findOneBy(['username' => $user]);
        $route = $this->routeRepo->findOneBy(['name' => $route]);
        if (empty($user) || empty($route) || !array_key_exists('_token', $post)) {
            $data['error'] = 'Erreur de saisie';

            return new JsonResponse($data);
        }

        $userId    = $user->getId();
        $routeId   = $route->getId();
        $csrfToken = new CsrfToken(
            'guard-'.$userId.'-route-'.$routeId,
            $post['_token']
        );
        if (!is_null($csrfToken) && $this->csrfTokenManager->isTokenValid($csrfToken)) {
            $data['error'] = 'token incorrect';

            return new JsonResponse($data);
        }

        $routeUser = $routeUserRepo->findOneBy(
            [
                'refuser'  => $user,
                'refroute' => $route,
            ]
        );
        if (!$routeUser instanceof RouteUser) {
            $routeUser = new RouteUser();
            $routeUser->setRefuser($user);
            $routeUser->setRefRoute($route);
        }

        $old = clone $routeUser;
        $routeUser->setState($post['state']);
        $routeUserRH->handle($old, $routeUser);
        $data['ok'] = true;

        return new JsonResponse($data);
    }

    /**
     * @Route("/users/{user}", name="api_guard_user")
     *
     * @return Response
     */
    public function user(
        User $user,
        RouteGroupeRepository $routeGroupeRepo,
        RouteUserRepository $routeUserRepo
    ): JsonResponse
    {
        $data    = [
            'groups' => [],
            'user'   => [],
        ];
        $results = $routeGroupeRepo->findEnable($user->getRefgroupe());
        foreach ($results as $row) {
            /* @var RouteGroupe $row */
            $data['groups'][] = [
                'route' => $row->getRefroute()->getName(),
            ];
        }

        $results = $routeUserRepo->findEnable($user);
        foreach ($results as $row) {
            /* @var RouteUser $row */
            $data['user'][] = [
                'route' => $row->getRefroute()->getName(),
            ];
        }

        return new JsonResponse($data);
    }

    private function getRefgroupe($routeGroupeRepo, ?Groupe $groupe = null)
    {
        $results = $routeGroupeRepo->findEnable($groupe);
        $data    = [];
        foreach ($results as $row) {
            /* @var RouteGroupe $row */
            $data[] = [
                'groupe' => $row->getRefgroupe()->getCode(),
                'route'  => $row->getRefroute()->getName(),
            ];
        }

        return new JsonResponse($data);
    }
}
