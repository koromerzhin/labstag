<?php

namespace Labstag\Block;

use Labstag\Entity\Block\Flashbag;
use Labstag\Form\Admin\Block\FlashbagType;
use Labstag\Interfaces\BlockInterface;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Lib\BlockLib;

class FlashbagBlock extends BlockLib implements BlockInterface
{
    public function context(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): mixed
    {
        unset($entityFront);
        if (!$entityBlock instanceof Flashbag) {
            return null;
        }

        return ['block' => $entityBlock];
    }

    public function getCode(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        unset($entityBlock, $entityFront);

        return 'flashbag';
    }

    public function getEntity(): string
    {
        return Flashbag::class;
    }

    public function getForm(): string
    {
        return FlashbagType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('flashbag.name', [], 'block');
    }

    public function getType(): string
    {
        return 'flashbag';
    }

    public function isShowForm(): bool
    {
        return false;
    }
}
