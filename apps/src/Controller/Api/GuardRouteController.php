<?php

namespace Labstag\Controller\Api;

use Labstag\Entity\Groupe;
use Labstag\Entity\Route as EntityRoute;
use Labstag\Entity\RouteGroupe;
use Labstag\Entity\RouteUser;
use Labstag\Entity\User;
use Labstag\Lib\ApiControllerLib;
use Labstag\RequestHandler\RouteGroupeRequestHandler;
use Labstag\RequestHandler\RouteUserRequestHandler;
use Labstag\Service\GuardService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/guard/route")
 */
class GuardRouteController extends ApiControllerLib
{
    /**
     * @Route("/group/{group}", name="api_guard_routegroup", methods={"POST"})
     */
    public function group(
        Groupe $group,
        GuardService $guardService,
        RouteGroupeRequestHandler $routeGroupeRH,
        Request $request
    )
    {
        $data   = [
            'delete' => 0,
            'add'    => 0,
            'error'  => '',
        ];
        $state  = $request->request->all('state');
        $routes = $guardService->getGuardRoutesForGroupe($group);
        // @var EntityRoute $route
        foreach ($routes as $route) {
            $data = $this->setRouteGroupe(
                $guardService,
                $data,
                $group,
                $route,
                $state,
                $routeGroupeRH
            );
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/groups/{route}", name="api_guard_routegroups", methods={"POST"})
     */
    public function groups(
        GuardService $guardService,
        EntityRoute $route,
        RouteGroupeRequestHandler $routeGroupeRH,
        Request $request
    )
    {
        $data    = [
            'delete' => 0,
            'add'    => 0,
            'error'  => '',
        ];
        $state   = $request->request->all('state');
        $groupes = $this->getRepository(Groupe::class)->findAll();
        foreach ($groupes as $group) {
            $data = $this->setRouteGroupe(
                $guardService,
                $data,
                $group,
                $route,
                $state,
                $routeGroupeRH
            );
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/", name="api_guard_route")
     */
    public function index(
        Request $request
    )
    {
        $data    = [
            'group' => [],
        ];
        $get     = $request->query->all();
        $data    = $this->getGuardRouteOrWorkflow($data, $get, RouteUser::class);
        $results = $this->getResultWorkflow($request, RouteGroupe::class);
        foreach ($results as $row) {
            // @var RouteGroupe $row
            $data['group'][] = [
                'groupe' => $row->getRefgroupe()->getCode(),
                'route'  => $row->getRefroute()->getName(),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/setgroup/{group}/{route}", name="api_guard_routesetgroup", methods={"POST"})
     */
    public function setgroup(
        GuardService $guardService,
        Groupe $group,
        EntityRoute $route,
        Request $request,
        RouteGroupeRequestHandler $routeGroupeRH
    )
    {
        $data  = [
            'delete' => 0,
            'add'    => 0,
            'error'  => '',
        ];
        $state = $request->request->all('state');
        $data  = $this->setRouteGroupe(
            $guardService,
            $data,
            $group,
            $route,
            $state,
            $routeGroupeRH
        );

        return new JsonResponse($data);
    }

    /**
     * @Route("/setuser/{user}/{route}", name="api_guard_routesetuser", methods={"POST"})
     */
    public function setuser(
        GuardService $guardService,
        User $user,
        EntityRoute $route,
        Request $request,
        RouteUserRequestHandler $routeUserRH
    )
    {
        $data  = [
            'delete' => 0,
            'add'    => 0,
            'error'  => '',
        ];
        $state = $request->request->all('state');
        $data  = $this->setRouteUser(
            $guardService,
            $data,
            $user,
            $state,
            $route,
            $routeUserRH
        );

        return new JsonResponse($data);
    }

    /**
     * @Route("/user/{user}", name="api_guard_routeuser", methods={"POST"})
     */
    public function user(
        GuardService $guardService,
        User $user,
        Request $request,
        RouteUserRequestHandler $routeUserRH
    )
    {
        $data   = [
            'delete' => 0,
            'add'    => 0,
            'error'  => '',
        ];
        $state  = $request->request->all('state');
        $routes = $guardService->getGuardRoutesForUser($user);
        // @var EntityRoute $route
        foreach ($routes as $route) {
            $data = $this->setRouteUser(
                $guardService,
                $data,
                $user,
                $state,
                $route,
                $routeUserRH
            );
        }

        return new JsonResponse($data);
    }

    private function setRouteGroupe(
        GuardService $guardService,
        $data,
        $group,
        $route,
        $state,
        $routeGroupeRH
    )
    {
        $routeGroupe = $this->getRepository(RouteGroupe::class)->findOneBy(
            [
                'refgroupe' => $group,
                'refroute'  => $route,
            ]
        );
        if ('0' === $state) {
            if ($routeGroupe instanceof RouteGroupe) {
                $data['delete'] = 1;
                $this->entityManager->remove($routeGroupe);
                $this->entityManager->flush();
            }

            return $data;
        }

        $enable = $guardService->guardRouteEnableGroupe($route->getName(), $group);
        if ('superadmin' === $group->getCode() || !$enable) {
            return $data;
        }

        if (!$routeGroupe instanceof RouteGroupe) {
            $routeGroupe = new RouteGroupe();
            $data['add'] = 1;
            $routeGroupe->setRefgroupe($group);
            $routeGroupe->setRefroute($route);
            $old = clone $routeGroupe;
            $routeGroupe->setState($state);
            $routeGroupeRH->handle($old, $routeGroupe);
        }

        return $data;
    }

    private function setRouteUser(
        guardService $guardService,
        array $data,
        $user,
        $state,
        EntityRoute $route,
        RouteUserRequestHandler $routeUserRH
    )
    {
        $routeUser = $this->getRepository(RouteUser::class)->findOneBy(['refuser' => $user, 'refroute' => $route]);
        if ('0' === $state) {
            if ($routeUser instanceof RouteUser) {
                $data['delete'] = 1;
                $this->entityManager->remove($routeUser);
                $this->entityManager->flush();
            }

            return $data;
        }

        $enable = $guardService->guardRouteEnableGroupe($route->getName(), $user->getRefgroupe());
        if ('superadmin' === $user->getRefgroupe()->getCode() || !$enable) {
            return $data;
        }

        if (!$routeUser instanceof RouteUser) {
            $data['add'] = 1;
            $routeUser   = new RouteUser();
            $routeUser->setRefuser($user);
            $routeUser->setRefroute($route);
            $old = clone $routeUser;
            $routeUser->setState($state);
            $routeUserRH->handle($old, $routeUser);
        }

        return $data;
    }
}
