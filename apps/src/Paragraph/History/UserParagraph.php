<?php

namespace Labstag\Paragraph\History;

use Labstag\Entity\History;
use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\History\User;
use Labstag\Form\Admin\Paragraph\History\UserType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\HistoryRepository;
use Symfony\Component\HttpFoundation\Request;

class UserParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $all        = $request->attributes->all();
        $routeParam = $all['_route_params'];
        $username   = $routeParam['username'] ?? null;
        /** @var HistoryRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(History::class);
        $pagination    = $this->paginator->paginate(
            $repositoryLib->findPublierUsername($username),
            $request->query->getInt('page', 1),
            10
        );

        return [
            'pagination' => $pagination,
            'paragraph'  => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['history/user'];
    }

    public function getEntity(): string
    {
        return User::class;
    }

    public function getForm(): string
    {
        return UserType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('historyuser.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'historyuser';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    /**
     * @return array<class-string<Layout>>
     */
    public function useIn(): array
    {
        return [
            Layout::class,
        ];
    }
}
