<?php

namespace Labstag\Paragraph\Edito;

use Labstag\Entity\Edito;
use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Edito\Show;
use Labstag\Form\Admin\Paragraph\Edito\ShowType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\EditoRepository;

class ShowParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var EditoRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Edito::class);
        $edito         = $repositoryLib->findOnePublier();

        if (!$edito instanceof Edito) {
            return null;
        }

        return [
            'edito'     => $edito,
            'paragraph' => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['edito/show'];
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
        return $this->translator->trans('editoshow.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'editoshow';
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
