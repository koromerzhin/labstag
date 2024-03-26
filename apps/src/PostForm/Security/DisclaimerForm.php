<?php

namespace Labstag\PostForm\Security;

use Labstag\Form\Security\DisclaimerType;
use Labstag\Interfaces\PostFormInterface;
use Labstag\Lib\PostFormLib;
use Symfony\Component\HttpFoundation\Request;

class DisclaimerForm extends PostFormLib implements PostFormInterface
{
    public function context(array $params): mixed
    {
        $form = $this->createForm(
            $this->getForm()
        );
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

        $config = $this->dataService->getConfig();
        if (1 == $session->get('disclaimer', 0)
            || !isset($config['disclaimer'])
            || !isset($config['disclaimer']['activate'])
            || 1 != $config['disclaimer']['activate']
        ) {
            return $this->redirectToRoute('front');
        }

        return [
            ...$params,
            'form' => $form,
        ];
    }

    public function getForm(): string
    {
        return DisclaimerType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('security-disclaimer.name', [], 'postform');
    }
}
