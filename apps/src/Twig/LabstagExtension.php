<?php

namespace Labstag\Twig;

use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Repository\GroupeRepository;
use Labstag\Service\GuardRouteService;
use Labstag\Service\PhoneService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Workflow\Registry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LabstagExtension extends AbstractExtension
{

    private PhoneService $phoneService;

    protected Registry $workflows;

    protected GuardRouteService $guardRouteService;

    protected TokenStorageInterface $token;

    protected GroupeRepository $groupeRepository;

    const REGEX_CONTROLLER_ADMIN = '/(Controller\\\Admin)/';

    public function __construct(
        PhoneService $phoneService,
        Registry $workflows,
        TokenStorageInterface $token,
        GroupeRepository $groupeRepository,
        GuardRouteService $guardRouteService
    )
    {
        $this->guardRouteService = $guardRouteService;
        $this->groupeRepository  = $groupeRepository;
        $this->workflows         = $workflows;
        $this->token             = $token;
        $this->phoneService      = $phoneService;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('workflow_has', [$this, 'workflowHas']),
            new TwigFilter('guard_route', [$this, 'guardRoute']),
            new TwigFilter('guard_route_enable_group', [$this, 'guardRouteEnableGroupe']),
            new TwigFilter('guard_route_enable_user', [$this, 'guardRouteEnableUser']),
            new TwigFilter('formClass', [$this, 'formClass']),
            new TwigFilter('verifPhone', [$this, 'verifPhone']),
            new TwigFilter('formPrototype', [$this, 'formPrototype']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('workflow_has', [$this, 'workflowHas']),
            new TwigFunction('guard_route', [$this, 'guardRoute']),
            new TwigFunction('guard_route_enable_group', [$this, 'guardRouteEnableGroupe']),
            new TwigFunction('guard_route_enable_user', [$this, 'guardRouteEnableUser']),
            new TwigFunction('formClass', [$this, 'formClass']),
            new TwigFunction('verifPhone', [$this, 'verifPhone']),
            new TwigFunction('formPrototype', [$this, 'formPrototype']),
        ];
    }

    public function guardRouteEnableUser(string $route, User $user): bool
    {
        $all = $this->guardRouteService->all();
        if (!array_key_exists($route, $all)) {
            return false;
        }

        $data     = $all[$route];
        $defaults = $data->getDefaults();
        $matches  = [];
        preg_match(self::REGEX_CONTROLLER_ADMIN, $defaults['_controller'], $matches);
        if (0 != count($matches) && 'visiteur' == $user->getGroupe()->getCode()) {
            return false;
        }

        return true;
    }

    public function guardRouteEnableGroupe(string $route, Groupe $groupe): bool
    {
        $all = $this->guardRouteService->all();
        if (!array_key_exists($route, $all)) {
            return false;
        }

        $data     = $all[$route];
        $defaults = $data->getDefaults();
        $matches  = [];
        preg_match(self::REGEX_CONTROLLER_ADMIN, $defaults['_controller'], $matches);
        if (0 != count($matches) && 'visiteur' == $groupe->getCode()) {
            return false;
        }

        return true;
    }

    public function guardRoute(string $route): bool
    {
        $all   = $this->guardRouteService->all();
        $token = $this->token->getToken();
        if (!array_key_exists($route, $all)) {
            return true;
        }

        $token = $this->token->getToken();
        if (empty($token) || !$token->getUser() instanceof User) {
            $groupe = $this->groupeRepository->findOneBy(['code' => 'visiteur']);
            if (!$this->guardRouteService->searchRouteGroupe($groupe, $route)) {
                return false;
            }

            return true;
        }

        /** @var User $user */
        $user   = $token->getUser();
        $groupe = $user->getGroupe();
        if ('superadmin' == $groupe->getCode()) {
            return true;
        }

        $state = $this->guardRouteService->searchRouteUser($user, $route);
        if (!$state) {
            return false;
        }

        return true;
    }

    public function workflowHas($entity)
    {
        return $this->workflows->has($entity);
    }

    public function verifPhone(string $country, string $phone)
    {
        $verif = $this->phoneService->verif($phone, $country);

        return array_key_exists('isvalid', $verif) ? $verif['isvalid'] : false;
    }

    public function formPrototype(array $blockPrefixes): string
    {
        $file = '';
        if ($blockPrefixes[1] != 'collection_entry') {
            return $file;
        }

        $type = $blockPrefixes[2];

        $newFile = 'prototype/'.$type.'.html.twig';
        if (!is_file(__DIR__.'/../../templates/'.$newFile)) {
            dump('Fichier manquant : '.__DIR__.'/../../templates/'.$newFile);

            return $file;
        }

        $file = $newFile;

        return $file;
    }

    private function setTypeformClass(array $class): string
    {
        if (is_object($class['data'])) {
            $tabClass = explode('\\', get_class($class['data']));
            $type     = end($tabClass);

            return $type;
        }

        $type = $class['form']->vars['unique_block_prefix'];

        return $type;
    }

    public function formClass($class)
    {
        $file = '';

        $methods = get_class_vars(get_class($class));
        if (!array_key_exists('vars', $methods)) {
            return $file;
        }

        $vars = $class->vars;

        if (!array_key_exists('data', $vars) || is_null($vars['data'])) {
            return $file;
        }

        $type = $this->setTypeformClass($vars);

        $newFile = 'forms/'.$type.'.html.twig';
        if (!is_file(__DIR__.'/../../templates/'.$newFile)) {
            dump('Fichier manquant : '.__DIR__.'/../../templates/'.$newFile);

            return $file;
        }

        $file = $newFile;

        return $file;

    }
}
