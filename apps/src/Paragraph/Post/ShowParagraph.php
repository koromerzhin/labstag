<?php

namespace Labstag\Paragraph\Post;

use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Post\Show;
use Labstag\Entity\Post;
use Labstag\Form\Admin\Paragraph\Post\ShowType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;

class ShowParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $all        = $request->attributes->all();
        $routeParam = $all['_route_params'];
        $slug       = $routeParam['slug'] ?? null;
        /** @var PostRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Post::class);
        $post          = $repositoryLib->findOneBy(
            ['slug' => $slug]
        );

        if (!$post instanceof Post) {
            return null;
        }

        return [
            'post'      => $post,
            'paragraph' => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['post/show'];
    }

    public function getEntity(): string
    {
        return Show::class;
    }

    public function getForm(): string
    {
        return ShowType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('postshow.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'postshow';
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
