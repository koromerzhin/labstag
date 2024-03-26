<?php

namespace Labstag\Block;

use Labstag\Entity\Block\Breadcrumb;
use Labstag\Form\Admin\Block\BreadcrumbType;
use Labstag\Interfaces\BlockInterface;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Lib\BlockLib;

class BreadcrumbBlock extends BlockLib implements BlockInterface
{
    public function context(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): mixed
    {
        if (!$entityBlock instanceof Breadcrumb) {
            return null;
        }

        $breadcrumbs = $this->frontService->setBreadcrumb($entityFront);
        if ((is_countable($breadcrumbs) ? count($breadcrumbs) : 0) <= 1) {
            return null;
        }

        return [
            'breadcrumbs' => $breadcrumbs,
            'block'       => $entityBlock,
        ];
    }

    public function getCode(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        unset($entityBlock, $entityFront);

        return 'breadcrumb';
    }

    public function getEntity(): string
    {
        return Breadcrumb::class;
    }

    public function getForm(): string
    {
        return BreadcrumbType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('breadcrumb.name', [], 'block');
    }

    public function getType(): string
    {
        return 'breadcrumb';
    }

    public function isShowForm(): bool
    {
        return false;
    }
}
