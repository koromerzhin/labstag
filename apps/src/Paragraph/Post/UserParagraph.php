<?php

namespace Labstag\Paragraph\Post;

use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Post\User;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\Post\UserType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserParagraph extends ParagraphLib implements ParagraphInterface
{
    public function getCode(EntityParagraphInterface $entityParagraph): string
    {
        unset($entityParagraph);

        return 'post/user';
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
        return $this->translator->trans('postuser.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'postuser';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function show(EntityParagraphInterface $entityParagraph): Response
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $all        = $request->attributes->all();
        $routeParam = $all['_route_params'];
        $username   = $routeParam['username'] ?? null;
        /** @var PostRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Post::class);
        $pagination    = $this->paginator->paginate(
            $repositoryLib->findPublierUsername($username),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            $this->getTemplateFile($this->getCode($entityParagraph)),
            [
                'pagination' => $pagination,
                'paragraph'  => $entityParagraph,
            ]
        );
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
