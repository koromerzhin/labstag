<?php

namespace Labstag\Twig;

use Labstag\Entity\Block;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Lib\ExtensionLib;
use Twig\TwigFilter;

class BlockExtension extends ExtensionLib
{
    public function getBlockClass(EntityBlockInterface $entityBlock): string
    {
        /** @var Block $block */
        $block     = $entityBlock->getBlock();
        $dataClass = [
            'block-'.$block->getType(),
        ];

        $dataClass = $this->blockService->getClassCss($dataClass, $block);

        return implode(' ', $dataClass);
    }

    public function getBlockId(EntityBlockInterface $entityBlock): string
    {
        /** @var Block $block */
        $block = $entityBlock->getBlock();

        return 'block-'.$block->getType().'-'.$block->getId();
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'block_id',
                fn (EntityBlockInterface $entityBlock): string => $this->getBlockId($entityBlock)
            ),
            new TwigFilter(
                'block_class',
                fn (EntityBlockInterface $entityBlock): string => $this->getBlockClass($entityBlock)
            ),
        ];
    }
}
