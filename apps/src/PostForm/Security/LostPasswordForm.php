<?php

namespace Labstag\PostForm\Security;

use Labstag\Form\Security\LostPasswordType;
use Labstag\Interfaces\PostFormInterface;
use Labstag\Lib\PostFormLib;
use Symfony\Component\HttpFoundation\Request;

class LostPasswordForm extends PostFormLib implements PostFormInterface
{
    public function context(array $params): mixed
    {
        $form = $this->createForm($this->getForm());
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $post = $request->request->all($form->getName());
            $this->userService->postLostPassword($post);

            return $this->redirectToRoute('app_login');
        }

        return [
            ...$params,
            'form' => $form,
        ];
    }

    public function getForm(): string
    {
        return LostPasswordType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('security-lostpassword.name', [], 'postform');
    }
}
