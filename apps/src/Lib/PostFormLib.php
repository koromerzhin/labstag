<?php

namespace Labstag\Lib;

use Labstag\Repository\OauthConnectUserRepository;
use Labstag\Service\DataService;
use Labstag\Service\RepositoryService;
use Labstag\Service\SessionService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class PostFormLib extends AbstractController
{
    public function __construct(
        protected WorkflowService $workflowService,
        protected UserService $userService,
        protected MailerInterface $mailer,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
        protected SessionService $sessionService,
        protected DataService $dataService,
        protected RouterInterface $router,
        protected RepositoryService $repositoryService,
        protected FormFactoryInterface $formFactory,
        protected AuthenticationUtils $authenticationUtils,
        protected OauthConnectUserRepository $oauthConnectUserRepository,
        protected Environment $twigEnvironment
    )
    {
    }

    protected function getEmailFrom(): string
    {
        return 'test@test.local';
    }

    protected function getTitle(): string
    {
        return 'SITE NAME';
    }

    protected function getToEmails(): array
    {
        return [];
    }
}
