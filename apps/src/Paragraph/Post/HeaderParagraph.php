<?php

namespace Labstag\Paragraph\Post;

use Labstag\Entity\Paragraph\Post\Header;
use Labstag\Form\Admin\Paragraph\Post\HeaderType;
use Labstag\Interfaces\EntityParagraphInterface;

class HeaderParagraph extends ShowParagraph
{
    public function getCode(EntityParagraphInterface $entityParagraph): array
    {
        unset($entityParagraph);

        return ['post/header'];
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
        return $this->translator->trans('postheader.name', [], 'paragraph');
    }

    public function getType(): string
    {
        return 'postheader';
    }

    public function isShowForm(): bool
    {
        return false;
    }
}
