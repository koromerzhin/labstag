<?php

namespace Labstag\Security;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    /**
     * @var string
     */
    final public const LOGIN_ROUTE = 'app_login';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $login = $request->request->all('login');
        if (!is_string($login['username']) || !is_string($login['password']) || !is_string($login['_csrf_token'])) {
            throw new Exception('Invalid login data');
        }

        $credentials = [
            'username'    => $login['username'],
            'password'    => $login['password'],
            '_csrf_token' => $login['_csrf_token'],
        ];
        $request->getSession()->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $credentials['username']
        );

        return new Passport(
            new UserBadge($credentials['username']),
            new PasswordCredentials($credentials['password']),
            [
                new CsrfTokenBadge('authenticate', $credentials['_csrf_token']),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        unset($token);
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        //return new RedirectResponse($this->urlGenerator->generate('some_route'));
        throw new Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        unset($request);

        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
