<?php

namespace Labstag\Paragraph\Edito;

use Labstag\Entity\Edito;
use Labstag\Entity\Layout;
use Labstag\Entity\Paragraph\Edito\Header;
use Labstag\Form\Admin\Paragraph\Edito\HeaderType;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Lib\ParagraphLib;
use Labstag\Repository\EditoRepository;

class HeaderParagraph extends ParagraphLib implements ParagraphInterface
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

        return ['edito/header'];
    }

    public function getEntity(): string
    {
        return Header::class;
    }

    public function getForm(): string
    {
        return HeaderType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('editoheader.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'editoheader';
    }

    public function isHeaderForm(): bool
    {
        return false;
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
