<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Edito as EntityEdito;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph\Edito;
use Labstag\Form\Admin\Paragraph\EditoType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\EditoRepository;

class EditoParagraph extends ParagraphLib implements ParagraphInterface
{
    public function context(EntityParagraphInterface $entityParagraph): mixed
    {
        /** @var EditoRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(EntityEdito::class);

        return [
            'edito'     => $repositoryLib->findOnePublier(),
            'paragraph' => $entityParagraph,
        ];
    }

    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['edito'];
    }

    public function getEntity(): string
    {
        return Edito::class;
    }

    public function getForm(): string
    {
        return EditoType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('edito.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'edito';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    public function useIn(): array
    {
        return [
            Page::class,
        ];
    }
}
