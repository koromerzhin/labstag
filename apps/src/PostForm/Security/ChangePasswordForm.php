<?php

namespace Labstag\PostForm\Security;

use Labstag\Entity\User;
use Labstag\Form\Security\ChangePasswordType;
use Labstag\Interfaces\PostFormInterface;
use Labstag\Lib\PostFormLib;
use Labstag\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordForm extends PostFormLib implements PostFormInterface
{
    public function context(array $params): mixed
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        if (!$request->attributes->has('id')) {
            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.user.sendlostpassword.fail')
            );

            return $this->redirectToRoute('front');
        }

        /** @var UserRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(User::class);
        $user          = $repositoryLib->findOneBy(
            [
                'id' => $request->attributes->get('id'),
            ]
        );

        if (!$user instanceof User || 'lostpassword' != $user->getState()) {
            $this->sessionService->flashBagAdd(
                'danger',
                $this->translator->trans('security.user.sendlostpassword.fail')
            );

            return $this->redirectToRoute('front');
        }

        $form = $this->createForm(
            $this->getForm(),
            $user
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->workflowService->changeState($user, ['valider']);

            return $this->redirectToRoute('front');
        }

        return [
            ...$params,
            'form' => $form,
        ];
    }

    public function getForm(): string
    {
        return ChangePasswordType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('security-disclaimer.name', [], 'postform');
    }
}
