<?php

namespace Labstag\PostForm\Security;

use Labstag\Form\Security\LoginType;
use Labstag\Interfaces\PostFormInterface;
use Labstag\Lib\PostFormLib;

class LoginForm extends PostFormLib implements PostFormInterface
{
    public function context(array $params): mixed
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

        return [
            ...$params,
            'form'   => $form,
            'oauths' => $oauths,
            'error'  => $authenticationException,
        ];
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
