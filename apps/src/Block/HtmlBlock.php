<?php

namespace Labstag\Block;

use Labstag\Entity\Block\Html;
use Labstag\Form\Admin\Block\HtmlType;
use Labstag\Interfaces\BlockInterface;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Lib\BlockLib;

class HtmlBlock extends BlockLib implements BlockInterface
{
    public function context(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): mixed
    {
        unset($entityFront);
        if (!$entityBlock instanceof Html) {
            return null;
        }

        return ['block' => $entityBlock];
    }

    public function getCode(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        unset($entityBlock, $entityFront);

        return 'html';
    }

    public function getEntity(): string
    {
        return Html::class;
    }

    public function getForm(): string
    {
        return HtmlType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('html.name', [], 'block');
    }

    public function getType(): string
    {
        return 'html';
    }

    public function isShowForm(): bool
    {
        return true;
    }
}
