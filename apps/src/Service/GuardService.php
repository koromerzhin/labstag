<?php

namespace Labstag\Service;

use Labstag\Entity\Groupe;
use Labstag\Entity\Route;
use Labstag\Entity\RouteGroupe;
use Labstag\Entity\RouteUser;
use Labstag\Entity\User;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\RouteGroupeRepository;
use Labstag\Repository\RouteRepository;
use Labstag\Repository\RouteUserRepository;
use Symfony\Component\Routing\Route as Routing;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GuardService
{
    /**
     * @var string[]
     */
    final public const GROUPE_ENABLE = ['visiteur'];

    /**
     * @var string[]
     */
    final public const REGEX = [
        '/(SecurityController)/',
        '/(web_profiler.controller)/',
        '/(error_controller)/',
        '/(api_platform)/',
    ];

    /**
     * @var string
     */
    final public const REGEX_CONTROLLER_ADMIN = '/(Controller\\\Admin)/';

    /**
     * @var string[]
     */
    final public const REGEX_PUBLIC = [
        '/(ElfinderBundle)/',
        '/(Admin)/',
        '/(ImagineBundle)/',
        '/(Api)/',
    ];

    public function __construct(
        protected RouterInterface $router,
        protected RepositoryService $repositoryService,
        protected GroupeRepository $groupeRepository,
        protected RouteRepository $routeRepository,
        protected RouteGroupeRepository $routeGroupeRepository,
        protected RouteUserRepository $routeUserRepository
    )
    {
    }

    /**
     * @return array<string, \Symfony\Component\Routing\Route>
     */
    public function all(): array
    {
        $data            = [];
        $routeCollection = $this->router->getRouteCollection();
        $all             = $routeCollection->all();
        foreach ($all as $name => $route) {
            /** @var Routing $route */
            $defaults = $route->getDefaults();
            if (!isset($defaults['_controller'])) {
                continue;
            }

            $matches = $this->regex($defaults['_controller']);
            if (0 != (is_countable($matches) ? count($matches) : 0)) {
                continue;
            }

            $data[$name] = $route;
        }

        return $data;
    }

    /**
     * @return array<string, \Symfony\Component\Routing\Route>
     */
    public function allRoutes(): array
    {
        $data            = [];
        $routeCollection = $this->router->getRouteCollection();
        $all             = $routeCollection->all();
        foreach ($all as $name => $route) {
            $data[$name] = $route;
        }

        return $data;
    }

    public function delete(): void
    {
        $results = $this->getLostRoute();
        if (!is_iterable($results)) {
            return;
        }

        foreach ($results as $result) {
            $this->routeRepository->remove($result);
        }
    }

    public function getGuardRoutesForGroupe(Groupe $groupe): array
    {
        $routes = $this->routesEnableGroupe($groupe);
        if ('superadmin' == $groupe->getCode()) {
            $routes = [];
        }

        return $routes;
    }

    public function getGuardRoutesForUser(User $user): array
    {
        $routes = $this->routesEnableUser($user);
        /** @var Groupe $groupe */
        $groupe = $user->getRefgroupe();
        if ('superadmin' == $groupe->getCode()) {
            $routes = [];
        }

        return $routes;
    }

    /**
     * @return array<string, \Symfony\Component\Routing\Route>
     */
    public function getPublicRoute(): array
    {
        $data            = [];
        $routeCollection = $this->router->getRouteCollection();
        $all             = $routeCollection->all();
        foreach ($all as $name => $route) {
            /** @var Routing $route */
            $defaults = $route->getDefaults();
            if (!isset($defaults['_controller'])) {
                continue;
            }

            $matches = $this->regexPublic($defaults['_controller']);
            if (0 != (is_countable($matches) ? count($matches) : 0)) {
                continue;
            }

            $data[$name] = $route;
        }

        ksort($data);

        return $data;
    }

    public function getPublicRouteWithParams(): array
    {
        $data = $this->getPublicRoute();
        if (isset($data['front'])) {
            unset($data['front']);
        }

        foreach ($data as $id => $row) {
            $defaults = $row->getDefaults();
            unset($defaults['_controller']);
            if (0 == (is_countable($defaults) ? count($defaults) : 0)) {
                continue;
            }

            unset($data[$id]);
        }

        return $data;
    }

    public function guardRoute(
        ?string $route,
        ?TokenInterface $token
    ): bool
    {
        $route = (string) $route;
        $all   = $this->all();
        if (!array_key_exists($route, $all)) {
            return true;
        }

        if (!$token instanceof TokenInterface || !$token->getUser() instanceof User) {
            /** @var Groupe $groupe */
            $groupe = $this->groupeRepository->findOneBy(['code' => 'visiteur']);

            return $this->searchRouteGroupe($groupe, $route);
        }

        /** @var User $user */
        $user = $token->getUser();
        /** @var Groupe $groupe */
        $groupe = $user->getRefgroupe();
        if ('superadmin' == $groupe->getCode()) {
            return true;
        }

        return $this->searchRouteUser($user, $route);
    }

    public function guardRouteEnableGroupe(Route $route, Groupe $groupe): bool
    {
        return $this->isRouteGroupe(
            $groupe,
            $route
        );
    }

    public function guardRouteEnableUser(Route $route, User $user): bool
    {
        /** @var Groupe $groupe */
        $groupe = $user->getRefgroupe();

        return $this->isRouteGroupe(
            $groupe,
            $route
        );
    }

    public function old(): array
    {
        $results = $this->getLostRoute();
        $data    = [];
        if (!is_iterable($results)) {
            return $data;
        }

        foreach ($results as $result) {
            $data[] = [$result];
        }

        return $data;
    }

    public function regex(string $string): array
    {
        $data = [];
        foreach (self::REGEX as $regex) {
            preg_match($regex, $string, $matches);
            foreach ($matches as $match) {
                $data[] = $match;
            }
        }

        return $data;
    }

    public function routesEnableGroupe(Groupe $groupe): array
    {
        $data   = $this->routeRepository->findBy([], ['name' => 'ASC']);
        $routes = [];
        foreach ($data as $route) {
            /** @var Route $route */
            $state = $this->guardRouteEnableGroupe($route, $groupe);
            if (!$state) {
                continue;
            }

            $routes[] = $route;
        }

        return $routes;
    }

    public function routesEnableUser(User $user): array
    {
        $data   = $this->routeRepository->findBy([], ['name' => 'ASC']);
        $routes = [];
        foreach ($data as $route) {
            /** @var Route $route */
            $state = $this->guardRouteEnableUser($route, $user);
            if (!$state) {
                continue;
            }

            $routes[] = $route;
        }

        return $routes;
    }

    public function save(string $name): void
    {
        $search = ['name' => $name];
        $result = $this->routeRepository->findOneBy(
            $search
        );

        if (!is_null($result)) {
            return;
        }

        $route = new Route();
        $route->setName($name);

        $this->routeRepository->save($route);
    }

    public function tables(): array
    {
        $data = [];
        $all  = $this->all();
        foreach ($all as $name => $route) {
            /** @var Routing $route */
            $defaults = $route->getDefaults();
            $data[]   = [
                $name,
                $defaults['_controller'],
            ];
        }

        return $data;
    }

    protected function searchRouteGroupe(Groupe $groupe, ?string $route): bool
    {
        $entity = $this->routeGroupeRepository->findRoute($groupe, $route);
        if (empty($entity)) {
            return false;
        }

        if (!$entity instanceof RouteGroupe) {
            return false;
        }

        $state = $entity->isState();

        return is_bool($state) ? $state : false;
    }

    protected function searchRouteUser(User $user, ?string $route): bool
    {
        $route = (string) $route;
        /** @var Groupe $groupe */
        $groupe      = $user->getRefgroupe();
        $stateGroupe = $this->searchRouteGroupe($groupe, $route);
        $entity      = $this->routeUserRepository->findRoute($user, $route);
        $stateUser   = ($entity instanceof RouteUser) ? $entity->isState() : false;

        return $stateGroupe || $stateUser;
    }

    private function getLostRoute(): mixed
    {
        $all    = $this->all();
        $routes = array_keys($all);

        return $this->routeRepository->findLost($routes);
    }

    private function isRouteGroupe(
        Groupe $groupe,
        string $route
    ): bool
    {
        $all = $this->all();
        if ('superadmin' == $groupe->getCode()) {
            return true;
        }

        if (!array_key_exists($route, $all)) {
            return false;
        }

        $data     = $all[$route];
        $defaults = $data->getDefaults();
        $matches  = [];
        preg_match(self::REGEX_CONTROLLER_ADMIN, (string) $defaults['_controller'], $matches);

        return !(0 != count($matches) && 'visiteur' == $groupe->getCode());
    }

    private function regexPublic(string $string): array
    {
        $data = $this->regex($string);
        foreach (self::REGEX_PUBLIC as $regex) {
            preg_match($regex, $string, $matches);
            foreach ($matches as $match) {
                $data[] = $match;
            }
        }

        return $data;
    }
}
