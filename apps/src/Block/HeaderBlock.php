<?php

namespace Labstag\Block;

use Labstag\Entity\Block\Header;
use Labstag\Form\Admin\Block\HeaderType;
use Labstag\Interfaces\BlockInterface;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Lib\BlockLib;

class HeaderBlock extends BlockLib implements BlockInterface
{
    public function context(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): mixed
    {
        unset($entityFront);
        if (!$entityBlock instanceof Header) {
            return null;
        }

        return ['block' => $entityBlock];
    }

    public function getCode(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        unset($entityBlock, $entityFront);

        return 'header';
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
        return $this->translator->trans('header.name', [], 'block');
    }

    public function getType(): string
    {
        return 'header';
    }

    public function isShowForm(): bool
    {
        return true;
    }
}
