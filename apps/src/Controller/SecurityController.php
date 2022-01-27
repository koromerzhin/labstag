<?php

namespace Labstag\Controller;

use Exception;
use Labstag\Entity\Email;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\Phone;
use Labstag\Entity\User;
use Labstag\Form\Security\ChangePasswordType;
use Labstag\Form\Security\DisclaimerType;
use Labstag\Form\Security\LoginType;
use Labstag\Form\Security\LostPasswordType;
use Labstag\Lib\ControllerLib;
use Labstag\RequestHandler\EmailRequestHandler;
use Labstag\RequestHandler\PhoneRequestHandler;
use Labstag\RequestHandler\UserRequestHandler;
use Labstag\Service\DataService;
use Labstag\Service\ErrorService;
use Labstag\Service\OauthService;
use Labstag\Service\UserService;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use LogicException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends ControllerLib
{
    #[Route(path: '/change-password/{id}', name: 'app_changepassword', priority: 1)]
    public function changePassword(User $user, Request $request, UserRequestHandler $requestHandler): Response
    {
        if ('lostpassword' != $user->getState()) {
            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.user.sendlostpassword.fail')
            );

            return $this->redirectToRoute('front');
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestHandler->changeWorkflowState($user, ['valider']);

            return $this->redirectToRoute('front');
        }

        return $this->renderForm(
            'security/change-password.html.twig',
            ['formChangePassword' => $form]
        );
    }

    #[Route(path: '/confirm/email/{id}', name: 'app_confirm_mail', priority: 1)]
    public function confirmEmail(Email $email, EmailRequestHandler $emailRequestHandler): RedirectResponse
    {
        if ('averifier' != $email->getState()) {
            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.email.activate.fail')
            );

            return $this->redirectToRoute('front');
        }

        $emailRequestHandler->changeWorkflowState($email, ['valider']);
        $this->sessionService->flashBagAdd(
            'success',
            $this->translator->trans('security.email.activate.win')
        );

        return $this->redirectToRoute('front');
    }

    #[Route(path: '/confirm/phone/{id}', name: 'app_confirm_phone', priority: 1)]
    public function confirmPhone(Phone $phone, PhoneRequestHandler $emailRequestHandler): RedirectResponse
    {
        if ('averifier' != $phone->getState()) {
            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.phone.activate.fail')
            );

            return $this->redirectToRoute('front');
        }

        $emailRequestHandler->changeWorkflowState($phone, ['valider']);
        $this->sessionService->flashBagAdd(
            'success',
            $this->translator->trans('security.phone.activate.win')
        );

        return $this->redirectToRoute('front');
    }

    #[Route(path: '/confirm/user/{id}', name: 'app_confirm_user', priority: 1)]
    public function confirmUser(User $user, UserRequestHandler $userRequestHandler): RedirectResponse
    {
        if ('avalider' != $user->getState()) {
            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.user.activate.fail')
            );

            return $this->redirectToRoute('front');
        }

        $userRequestHandler->changeWorkflowState($user, ['validation']);
        $this->sessionService->flashBagAdd(
            'success',
            $this->translator->trans('security.user.activate.win')
        );

        return $this->redirectToRoute('front');
    }

    #[Route(path: '/disclaimer', name: 'disclaimer', priority: 1)]
    public function disclaimer(Request $request, DataService $dataService): RedirectResponse|Response
    {
        $form = $this->createForm(DisclaimerType::class, []);
        $form->handleRequest($request);
        $session = $request->getSession();
        if ($form->isSubmitted()) {
            $post = $request->request->all($form->getName());
            if (isset($post['confirm'])) {
                $session->set('disclaimer', 1);

                return $this->redirectToRoute('front');
            }

            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.disclaimer.doaccept')
            );
        }

        $config = $dataService->getConfig();
        if (1 == $session->get('disclaimer', 0)
            || !isset($config['disclaimer'])
            || !isset($config['disclaimer']['activate'])
            || 1 != $config['disclaimer']['activate']
        ) {
            return $this->redirectToRoute('front');
        }

        return $this->renderForm(
            'security/disclaimer.html.twig',
            [
                'class_body' => 'DisclaimerPage',
                'form'       => $form,
            ]
        );
    }

    #[Route(path: '/login', name: 'app_login', priority: 1)]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $form         = $this->createForm(
            LoginType::class,
            ['username' => $lastUsername]
        );
        $oauths       = $this->getRepository(OauthConnectUser::class)->findDistinctAllOauth();

        return $this->renderForm(
            'security/login.html.twig',
            [
                'oauths'    => $oauths,
                'formLogin' => $form,
                'error'     => $error,
            ]
        );
    }

    #[Route(path: '/logout', name: 'app_logout', priority: 1)]
    public function logout(): never
    {
        $msg = 'This method can be blank - it will be intercepted by the logout key on your firewall.';

        throw new LogicException($msg);
    }

    #[Route(path: '/lost', name: 'app_lost', priority: 1)]
    public function lost(Request $request, UserService $userService): Response
    {
        $form = $this->createForm(LostPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $post = $request->request->all($form->getName());
            $userService->postLostPassword($post);

            return $this->redirectToRoute('app_login');
        }

        return $this->renderForm(
            'security/lost-password.html.twig',
            ['formLostPassword' => $form]
        );
    }

    /**
     * Link to this controller to start the "connect" process.
     */
    #[Route(path: '/oauth/connect/{oauthCode}', name: 'connect_start', priority: 1)]
    public function oauthConnect(Request $request, string $oauthCode, OauthService $oauthService): RedirectResponse
    {
        // @var AbstractProvider $provider
        $provider = $oauthService->setProvider($oauthCode);
        $session  = $request->getSession();
        // @var string $referer
        $query = $request->query->all();
        if (array_key_exists('link', $query)) {
            $session->set('link', 1);
        }

        $referer = $request->headers->get('referer');
        $session->set('referer', $referer);
        // @var string $url
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        if (!$provider instanceof AbstractProvider) {
            $this->sessionService->flashBagAdd(
                'warning',
                $this->translator->trans('security.user.oauth.fail')
            );

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
     */
    #[Route(path: '/oauth/check/{oauthCode}', name: 'connect_check', priority: 1)]
    public function oauthConnectCheck(
        Request $request,
        string $oauthCode,
        UsageTrackingTokenStorage $tokenStorage,
        ErrorService $errorService,
        OauthService $oauthService,
        UserService $userService
    ): RedirectResponse
    {
        // @var AbstractProvider $provider
        $provider    = $oauthService->setProvider($oauthCode);
        $query       = $request->query->all();
        $session     = $request->getSession();
        $referer     = $session->get('referer');
        $oauth2state = $session->get('oauth2state');
        // @var string $url
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        if ($userService->ifBug($provider, $query, $oauth2state)) {
            $session->remove('oauth2state');
            $session->remove('referer');
            $session->remove('link');
            $this->sessionService->flashBagAdd(
                'warning',
                $this->translator->trans('security.user.connect.fail')
            );

            return $this->redirect($referer);
        }

        try {
            // @var AccessToken $tokenProvider
            $tokenProvider = $provider->getAccessToken(
                'authorization_code',
                [
                    'code' => $query['code'],
                ]
            );

            $session->remove('oauth2state');
            $userOauth = $provider->getResourceOwner($tokenProvider);
            // @var TokenInterface $token
            $user = $tokenStorage->getToken()->getUser();
            if (!$user instanceof User) {
                $this->sessionService->flashBagAdd(
                    'warning',
                    $this->translator->trans('security.user.connect.fail')
                );

                return $this->redirect($referer);
            }

            $userService->addOauthToUser(
                $oauthCode,
                $user,
                $userOauth
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
    public function oauthLost(Request $request, string $oauthCode, Security $security): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // @var User $user
        $user = $security->getUser();
        // @var string $referer
        $referer = $request->headers->get('referer');
        $session = $request->getSession();
        $session->set('referer', $referer);
        // @var string $url
        $url = $this->generateUrl('front');
        if ('' == $referer) {
            $referer = $url;
        }

        $entity = $this->getRepository(OauthConnectUser::class)->findOneOauthByUser($oauthCode, $user);
        if ($entity instanceof OauthConnectUser) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            $paramtrans = ['%string%' => $oauthCode];

            $msg = $this->translator->trans('security.user.oauth.dissociated', $paramtrans);
            $this->sessionService->flashBagAdd('success', $msg);
        }

        return $this->redirect($referer);
    }
}
