<?php

namespace Labstag\Security;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\User;
use Labstag\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator as AbstractAuth;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface as PassAuthInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class FormAuthenticator extends AbstractAuth implements PassAuthInterface
{
    use TargetPathTrait;
    public const LOGIN_ROUTE = 'app_login';

    protected CsrfTokenManagerInterface $csrfTokenManager;

    protected EntityManagerInterface $entityManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    protected UserRepository $repository;

    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $repository
    )
    {
        $this->repository       = $repository;
        $this->entityManager    = $entityManager;
        $this->urlGenerator     = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder  = $passwordEncoder;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid(
            $user,
            $credentials['password']
        );
    }

    public function getCredentials(Request $request)
    {
        $login       = $request->request->get('login');
        $credentials = [
            'username'    => $login['username'],
            'password'    => $login['password'],
            '_csrf_token' => $login['_csrf_token'],
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        unset($userProvider);
        $token = new CsrfToken('authenticate', $credentials['_csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->repository->findUserEnable(($credentials['username']));
        if (!$user instanceof User) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $providerKey
    )
    {
        unset($token);
        $newTarget = $this->getTargetPath($request->getSession(), $providerKey);

        return new RedirectResponse($newTarget);
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }
}
