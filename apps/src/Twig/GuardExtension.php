<?php

namespace Labstag\Twig;

use Labstag\Entity\Groupe;
use Labstag\Entity\Route;
use Labstag\Entity\User;
use Labstag\Lib\ExtensionLib;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GuardExtension extends ExtensionLib
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'guard_group_access',
                fn (Groupe $groupe): bool => $this->guardAccessGroupRoutes($groupe)
            ),
            new TwigFilter(
                'guard_route',
                fn (string $route): bool => $this->guardRoute($route)
            ),
            new TwigFilter(
                'guard_user_access',
                fn (User $user): bool => $this->guardAccessUserRoutes($user)
            ),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'guard_route_enable_group',
                fn (Route $route, Groupe $groupe): bool => $this->guardRouteEnableGroupe($route, $groupe)
            ),
        ];
    }

    public function guardAccessGroupRoutes(Groupe $groupe): bool
    {
        $routes = $this->guardService->getGuardRoutesForGroupe($groupe);

        return 0 != count($routes);
    }

    public function guardAccessUserRoutes(User $user): bool
    {
        $routes = $this->guardService->getGuardRoutesForUser($user);

        return 0 != count($routes);
    }

    public function guardRoute(string $route): bool
    {
        $token = $this->tokenStorage->getToken();

        return $this->guardService->guardRoute($route, $token);
    }

    public function guardRouteEnableGroupe(Route $route, Groupe $groupe): bool
    {
        return $this->guardService->guardRouteEnableGroupe($route, $groupe);
    }
}
