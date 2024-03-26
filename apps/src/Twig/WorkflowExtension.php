<?php

namespace Labstag\Twig;

use Labstag\Interfaces\EntityInterface;
use Labstag\Lib\ExtensionLib;
use Twig\TwigFilter;

class WorkflowExtension extends ExtensionLib
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'workflow_has',
                fn (EntityInterface $entity): bool => $this->workflowHas($entity)
            ),
        ];
    }

    public function workflowHas(EntityInterface $entity): bool
    {
        return $this->workflowService->has($entity);
    }
}
