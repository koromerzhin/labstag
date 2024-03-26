<?php

namespace Labstag\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Queue\EnqueueMethod;
use Labstag\Service\BlockService;
use Labstag\Service\DataService;
use Labstag\Service\ErrorService;
use Labstag\Service\FrontService;
use Labstag\Service\GuardService;
use Labstag\Service\HttpErrorService;
use Labstag\Service\ParagraphService;
use Labstag\Service\RepositoryService;
use Labstag\Service\SessionService;
use Labstag\Service\UserMailService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class EventSubscriberLib implements EventSubscriberInterface
{
    public function __construct(
        protected HttpErrorService $httpErrorService,
        protected EntityManagerInterface $entityManager,
        protected ParagraphService $paragraphService,
        protected BlockService $blockService,
        protected Environment $twigEnvironment,
        protected FrontService $frontService,
        protected UrlGeneratorInterface $urlGenerator,
        protected CsrfTokenManagerInterface $csrfTokenManager,
        protected Security $security,
        protected ErrorService $errorService,
        protected RouterInterface $router,
        protected DataService $dataService,
        protected GuardService $guardService,
        protected TokenStorageInterface $tokenStorage,
        protected LoggerInterface $logger,
        protected ParameterBagInterface $parameterBag,
        protected RepositoryService $repositoryService,
        protected EnqueueMethod $enqueueMethod,
        protected CacheInterface $cache,
        protected SessionService $sessionService,
        protected RequestStack $requestStack,
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected UserMailService $userMailService,
        protected TranslatorInterface $translator
    )
    {
    }
}
