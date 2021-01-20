<?php

namespace Labstag\Controller;

use Exception;
use Labstag\Entity\Email;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\User;
use Labstag\Lib\GenericProviderLib;
use Labstag\Form\Security\ChangePasswordType;
use Labstag\Form\Security\DisclaimerType;
use Symfony\Component\HttpFoundation\Response;
use Labstag\Form\Security\LoginType;
use Labstag\Form\Security\LostPasswordType;
use Labstag\Lib\ControllerLib;
use Labstag\Repository\OauthConnectUserRepository;
use Labstag\RequestHandler\EmailRequestHandler;
use Labstag\RequestHandler\UserRequestHandler;
use Labstag\Service\DataService;
use Labstag\Service\OauthService;
use Labstag\Service\UserService;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class SecurityController extends ControllerLib
{

    private OauthService $oauthService;

    private LoggerInterface $logger;

    private UserService $userService;

    public function __construct(
        OauthService $oauthService,
        LoggerInterface $logger,
        DataService $dataService,
        Breadcrumbs $breadcrumbs,
        UserService $userService
    )
    {
        $this->userService  = $userService;
        $this->logger       = $logger;
        $this->oauthService = $oauthService;
        parent::__construct($dataService, $breadcrumbs);
    }

    /**
     * Link to this controller to start the "connect" process.
     *
     * @Route("/oauth/lost/{oauthCode}", name="connect_lost")
     */
    public function oauthLost(
        Request $request,
        string $oauthCode,
        Security $security,
        OauthConnectUserRepository $repository
    ): RedirectResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        $session = $request->getSession();
        $session->set('referer', $referer);
        /** @var string $url */
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        $entity  = $repository->findOneOauthByUser($oauthCode, $user);
        $manager = $this->getDoctrine()->getManager();
        if ($entity instanceof OauthConnectUser) {
            $manager->remove($entity);
            $manager->flush();
            $this->addFlash(
                'success',
                'Connexion Oauh ' . $oauthCode . ' dissocié'
            );
        }

        return $this->redirect($referer);
    }

    /**
     * Link to this controller to start the "connect" process.
     *
     * @Route("/oauth/connect/{oauthCode}", name="connect_start")
     */
    public function oauthConnect(
        Request $request,
        string $oauthCode
    ): RedirectResponse
    {
        /** @var GenericProviderLib $provider */
        $provider = $this->oauthService->setProvider($oauthCode);
        $session  = $request->getSession();
        /** @var string $referer */
        $referer = $request->headers->get('referer');
        $session->set('referer', $referer);
        /** @var string $url */
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        if (!($provider instanceof GenericProviderLib)) {
            $this->addFlash('warning', 'Connexion Oauh impossible');

            return $this->redirect($referer);
        }

        $authUrl = $provider->getAuthorizationUrl();
        $session = $request->getSession();
        $referer = $request->headers->get('referer');
        $session->set('referer', $referer);
        $session->set('oauth2state', $provider->getState());

        return $this->redirect($authUrl);
    }

    /**
     * After going to Github, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml.
     *
     * @Route("/oauth/connect/{oauthCode}/check", name="connect_check")
     */
    public function oauthConnectCheck(
        Request $request,
        string $oauthCode
    ): RedirectResponse
    {
        /** @var GenericProviderLib $provider */
        $provider    = $this->oauthService->setProvider($oauthCode);
        $query       = $request->query->all();
        $session     = $request->getSession();
        $referer     = $session->get('referer');
        $oauth2state = $session->get('oauth2state');
        /** @var string $url */
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        if ($this->userService->ifBug($provider, $query, $oauth2state)) {
            $session->remove('oauth2state');
            $session->remove('referer');
            $this->addFlash('warning', "Probleme d'identification");

            return $this->redirect($referer);
        }

        try {
            /** @var AccessToken $tokenProvider */
            $tokenProvider = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $query['code'],
                ]
            );

            $session->remove('oauth2state');
            $session->remove('referer');
            /** @var UsageTrackingTokenStorage $tokenStorage */
            $tokenStorage = $this->get('security.token_storage');
            /** @var TokenInterface $token */
            $token = $tokenStorage->getToken();
            if (!($token instanceof AnonymousToken)) {
                /** @var ResourceOwnerInterface $userOauth */
                $userOauth = $provider->getResourceOwner($tokenProvider);
                /** @var User $user */
                $user = $token->getUser();
                if (!($user instanceof User)) {
                    $this->addFlash('warning', "Probleme d'identification");

                    return $this->redirect($referer);
                }

                $this->userService->addOauthToUser(
                    $oauthCode,
                    $user,
                    $userOauth
                );
            }

            return $this->redirect($referer);
        } catch (Exception $exception) {
            $errorMsg = sprintf(
                'Exception : Erreur %s dans %s L.%s : %s',
                $exception->getCode(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getMessage()
            );
            $this->logger->error($errorMsg);
            $this->addFlash('warning', "Probleme d'identification");

            return $this->redirect($referer);
        }
    }

    /**
     * @Route("/disclaimer", name="disclaimer")
     *
     * @return RedirectResponse|Response
     */
    public function disclaimer(Request $request)
    {
        $form = $this->createForm(DisclaimerType::class, []);
        $form->handleRequest($request);
        $session = $request->getSession();
        if ($form->isSubmitted()) {
            $post = $request->request->get($form->getName());
            if (isset($post['confirm'])) {
                $session->set('disclaimer', 1);

                return $this->redirect(
                    $this->generateUrl('front')
                );
            }

            $this->addFlash('danger', "Veuillez accepter l'énoncé");
        }

        if (1 == $session->get('disclaimer', 0)) {
            return $this->redirect(
                $this->generateUrl('front')
            );
        }

        return $this->render(
            'security/disclaimer.html.twig',
            [
                'class_body' => 'DisclaimerPage',
                'form'       => $form->createView(),
            ]
        );
    }
    /**
     * @Route("/login", name="app_login")
     */
    public function login(
        AuthenticationUtils $authenticationUtils,
        OauthConnectUserRepository $repository
    ): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $form         = $this->createForm(
            LoginType::class,
            ['username' => $lastUsername]
        );

        $oauths = $repository->findDistinctAllOauth();

        return $this->render(
            'security/login.html.twig',
            [
                'oauths'    => $oauths,
                'formLogin' => $form->createView(),
                'error'     => $error,
            ]
        );
    }

    /**
     * @Route("/confirm/email/{id}", name="app_confirm_mail")
     */
    public function confirmEmail(
        Email $email,
        EmailRequestHandler $emailRequestHandler
    ): RedirectResponse
    {
        if ($email->isVerif()) {
            $this->addFlash('danger', 'Courriel déjà confirmé');

            return $this->redirect($this->generateUrl('front'), 302);
        }

        $email->setVerif(true);
        $old = clone $email;
        $emailRequestHandler->update($old, $clone);
        $this->addFlash('success', 'Courriel confirmé');

        return $this->redirect($this->generateUrl('front'), 302);
    }

    /**
     * @Route("/confirm/user/{id}", name="app_confirm_user")
     */
    public function confirmUser(
        User $user,
        UserRequestHandler $userRequestHandler
    ): RedirectResponse
    {
        if ($user->isVerif()) {
            $this->addFlash('danger', 'Utilisation déjà activé');

            return $this->redirect($this->generateUrl('front'), 302);
        }

        $user->setVerif(true);
        $old = clone $user;
        $userRequestHandler->update($old, $user);
        $this->addFlash('success', 'Utilisation activé');

        return $this->redirect($this->generateUrl('front'), 302);
    }

    /**
     * @Route("/change-password/{id}", name="app_changepassword")
     */
    public function changePassword(
        User $user,
        Request $request,
        UserRequestHandler $requestHandler
    ): Response
    {
        if (!$user->isLost()) {
            $this->addFlash('danger', 'Demande de mot de passe non envoyé');

            return $this->redirect($this->generateUrl('front'), 302);
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $old = clone $user;
            $user->setLost(false);
            $requestHandler->update($old, $user);

            return $this->redirect($this->generateUrl('front'), 302);
        }

        return $this->render(
            'security/change-password.html.twig',
            [
                'formChangePassword' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/lost", name="app_lost")
     *
     */
    public function lost(Request $request): Response
    {
        $form = $this->createForm(LostPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $post = $request->request->get($form->getName());
            $this->userService->postLostPassword($post);

            return $this->redirect($this->generateUrl('app_login'), 302);
        }

        return $this->render(
            'security/lost-password.html.twig',
            [
                'formLostPassword' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
    }
}
