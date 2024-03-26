<?php

namespace Labstag\Controller;

use Exception;
use Labstag\Entity\Email;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\Phone;
use Labstag\Entity\User;
use Labstag\Lib\FrontControllerLib;
use Labstag\Repository\OauthConnectUserRepository;
use Labstag\Repository\PageRepository;
use Labstag\Service\ErrorService;
use Labstag\Service\OauthService;
use Labstag\Service\SessionService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends FrontControllerLib
{
    #[Route(path: '/change-password/{id}', name: 'app_changepassword', priority: 1)]
    public function changePassword(
        PageRepository $pageRepository
    ): Response
    {
        return $this->page('change-password', $pageRepository);
    }

    #[Route(path: '/confirm/email/{id}', name: 'app_confirm_mail', priority: 1)]
    public function confirmEmail(
        TranslatorInterface $translator,
        WorkflowService $workflowService,
        SessionService $sessionService,
        Email $email
    ): RedirectResponse
    {
        if ('averifier' != $email->getState()) {
            $sessionService->flashBagAdd(
                'danger',
                $translator->trans('security.email.activate.fail')
            );

            return $this->redirectToRoute('front');
        }

        $workflowService->changeState($email, ['valider']);
        $sessionService->flashBagAdd(
            'success',
            $translator->trans('security.email.activate.win')
        );

        return $this->redirectToRoute('front');
    }

    #[Route(path: '/confirm/phone/{id}', name: 'app_confirm_phone', priority: 1)]
    public function confirmPhone(
        TranslatorInterface $translator,
        WorkflowService $workflowService,
        SessionService $sessionService,
        Phone $phone
    ): RedirectResponse
    {
        if ('averifier' != $phone->getState()) {
            $sessionService->flashBagAdd(
                'danger',
                $translator->trans('security.phone.activate.fail')
            );

            return $this->redirectToRoute('front');
        }

        $workflowService->changeState($phone, ['valider']);
        $sessionService->flashBagAdd(
            'success',
            $translator->trans('security.phone.activate.win')
        );

        return $this->redirectToRoute('front');
    }

    #[Route(path: '/confirm/user/{id}', name: 'app_confirm_user', priority: 1)]
    public function confirmUser(
        TranslatorInterface $translator,
        WorkflowService $workflowService,
        SessionService $sessionService,
        User $user
    ): RedirectResponse
    {
        if ('avalider' != $user->getState()) {
            $sessionService->flashBagAdd(
                'danger',
                $translator->trans('security.user.activate.fail')
            );

            return $this->redirectToRoute('front');
        }

        $workflowService->changeState($user, ['validation']);
        $sessionService->flashBagAdd(
            'success',
            $translator->trans('security.user.activate.win')
        );

        return $this->redirectToRoute('front');
    }

    #[Route(path: '/disclaimer', name: 'disclaimer', priority: 1)]
    public function disclaimer(
        PageRepository $pageRepository
    ): Response
    {
        return $this->page('disclaimer', $pageRepository);
    }

    #[Route(path: '/login', name: 'app_login', priority: 1)]
    public function login(
        PageRepository $pageRepository
    ): Response
    {
        return $this->page('login', $pageRepository);
    }

    #[Route(path: '/logout', name: 'app_logout', priority: 1)]
    public function logout(): never
    {
        $msg = 'This method can be blank - it will be intercepted by the logout key on your firewall.';

        throw new LogicException($msg);
    }

    #[Route(path: '/lost', name: 'app_lost', priority: 1)]
    public function lost(
        PageRepository $pageRepository
    ): Response
    {
        return $this->page('lost', $pageRepository);
    }

    /**
     * Link to this controller to start the "connect" process.
     */
    #[Route(path: '/oauth/connect/{oauthCode}', name: 'connect_start', priority: 1)]
    public function oauthConnect(
        TranslatorInterface $translator,
        SessionService $sessionService,
        Request $request,
        string $oauthCode,
        OauthService $oauthService
    ): RedirectResponse
    {
        /** @var AbstractProvider $provider */
        $provider = $oauthService->setProvider($oauthCode);
        $session  = $request->getSession();
        $query    = $request->query->all();
        if (array_key_exists('link', $query)) {
            $session->set('link', 1);
        }

        $referer = $request->headers->get('referer');
        $session->set('referer', $referer);
        /** @var string $url */
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        if (!$provider instanceof AbstractProvider) {
            $sessionService->flashBagAdd(
                'warning',
                $translator->trans('security.user.oauth.fail')
            );

            return $this->redirect((string) $referer);
        }

        $authorizationUrl = $provider->getAuthorizationUrl();
        $session          = $request->getSession();
        $referer          = $request->headers->get('referer');
        $session->set('referer', $referer);
        $session->set('oauth2state', $provider->getState());

        return $this->redirect($authorizationUrl);
    }

    /**
     * After going to Github, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml.
     */
    #[Route(path: '/oauth/check/{oauthCode}', name: 'connect_check', priority: 1)]
    public function oauthConnectCheck(
        TranslatorInterface $translator,
        SessionService $sessionService,
        Request $request,
        string $oauthCode,
        UsageTrackingTokenStorage $usageTrackingTokenStorage,
        ErrorService $errorService,
        OauthService $oauthService,
        UserService $userService
    ): RedirectResponse
    {
        /** @var AbstractProvider $provider */
        $provider = $oauthService->setProvider($oauthCode);
        $query    = $request->query->all();
        $session  = $request->getSession();
        $referer  = $session->get('referer');
        if (!is_string($referer)) {
            $referer = '';
        }

        /** @var string $url */
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        $oauth2state = $session->get('oauth2state');
        if (!is_string($oauth2state)) {
            return $this->redirect($referer);
        }

        if ($userService->ifBug($provider, $query, $oauth2state)) {
            $session->remove('oauth2state');
            $session->remove('referer');
            $session->remove('link');
            $sessionService->flashBagAdd(
                'warning',
                $translator->trans('security.user.connect.fail')
            );

            return $this->redirect($referer);
        }

        try {
            /** @var AccessToken $accessToken */
            $accessToken = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $query['code'],
                ]
            );

            $session->remove('oauth2state');
            $resourceOwner = $provider->getResourceOwner($accessToken);
            /** @var TokenInterface $token */
            $token = $usageTrackingTokenStorage->getToken();
            /** @var User $user */
            $user = $token->getUser();
            if (!$user instanceof User) {
                $sessionService->flashBagAdd(
                    'warning',
                    $translator->trans('security.user.connect.fail')
                );

                return $this->redirect($referer);
            }

            $userService->addOauthToUser(
                $oauthCode,
                $user,
                $resourceOwner
            );

            $session->remove('referer');
            $session->remove('link');

            return $this->redirect($referer);
        } catch (Exception $exception) {
            $errorService->set($exception);
            $session->remove('referer');
            $session->remove('link');

            return $this->redirect($referer);
        }
    }

    /**
     * Link to this controller to start the "connect" process.
     */
    #[Route(path: '/oauth/lost/{oauthCode}', name: 'connect_lost', priority: 1)]
    public function oauthLost(
        TranslatorInterface $translator,
        SessionService $sessionService,
        Request $request,
        string $oauthCode,
        Security $security,
        OauthConnectUserRepository $oauthConnectUserRepository
    ): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
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

        $oauthConnectUser = $oauthConnectUserRepository->findOneOauthByUser($oauthCode, $user);
        if ($oauthConnectUser instanceof OauthConnectUser) {
            $oauthConnectUserRepository->remove($oauthConnectUser);
            $paramtrans = ['%string%' => $oauthCode];

            $msg = $translator->trans('security.user.oauth.dissociated', $paramtrans);
            $sessionService->flashBagAdd('success', $msg);
        }

        return $this->redirect($referer);
    }
}
