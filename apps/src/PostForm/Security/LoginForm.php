<?php

namespace Labstag\PostForm\Security;

use Labstag\Form\Security\LoginType;
use Labstag\Interfaces\PostFormInterface;
use Labstag\Lib\PostFormLib;
use Symfony\Component\HttpFoundation\Response;

class LoginForm extends PostFormLib implements PostFormInterface
{
    public function execute(string $template, array $params): ?Response
    {
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $form         = $this->createForm(
            $this->getForm(),
            ['username' => $lastUsername]
        );
        // get the login error if there is one
        $authenticationException = $this->authenticationUtils->getLastAuthenticationError();
        $oauths                  = $this->oauthConnectUserRepository->findDistinctAllOauth();

        return $this->render(
            $template,
            array_merge(
                $params,
                [
                    'form'   => $form,
                    'oauths' => $oauths,
                    'error'  => $authenticationException,
                ]
            )
        );
    }

    public function context(string $template, array $params): mixed
    {
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $form         = $this->createForm(
            $this->getForm(),
            ['username' => $lastUsername]
        );
        // get the login error if there is one
        $authenticationException = $this->authenticationUtils->getLastAuthenticationError();
        $oauths                  = $this->oauthConnectUserRepository->findDistinctAllOauth();

        return array_merge(
            $params,
            [
                'form'   => $form,
                'oauths' => $oauths,
                'error'  => $authenticationException,
            ]
        );
    }

    public function getForm(): string
    {
        return LoginType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('security-login.name', [], 'postform');
    }
}
