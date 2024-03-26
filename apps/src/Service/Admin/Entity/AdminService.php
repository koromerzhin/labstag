<?php

namespace Labstag\Service\Admin\Entity;

use Labstag\Entity\Memo;
use Labstag\Form\Admin\FormType;
use Labstag\Interfaces\AdminEntityServiceInterface;
use Labstag\Repository\MemoRepository;
use Labstag\Service\Admin\ViewService;
use Symfony\Component\HttpFoundation\Response;

class AdminService extends ViewService implements AdminEntityServiceInterface
{
    public function getType(): string
    {
        return 'admin';
    }

    public function home(): Response
    {
        /** @var MemoRepository $memoRepository */
        $memoRepository = $this->entityManager->getRepository(Memo::class);
        $memos          = $memoRepository->findPublier();

        return $this->render(
            'admin/index.html.twig',
            ['memos' => $memos]
        );
    }

    public function oauth(): Response
    {
        $types = $this->oauthService->getConfigProvider();

        return $this->render(
            'admin/oauth.html.twig',
            ['types' => $types]
        );
    }

    public function themes(string $state): Response
    {
        $data = [
            'buttons'     => [[]],
            'choice'      => [[]],
            'dateandtime' => [[]],
            'hidden'      => [[]],
            'extra'       => [[]],
            'other'       => [[]],
            'text'        => [[]],
            'collection'  => [[]],
        ];
        $form = $this->createForm(FormType::class, $data);

        return $this->render(
            $state.'/form.html.twig',
            ['form' => $form]
        );
    }
}
