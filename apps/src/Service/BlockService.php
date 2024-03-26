<?php

namespace Labstag\Service;

use Doctrine\ORM\PersistentCollection;
use Labstag\Entity\Block;
use Labstag\Interfaces\BlockInterface;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Queue\EnqueueMethod;
use Labstag\Repository\BlockRepository;
use Labstag\Repository\PageRepository;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class BlockService
{

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('blocksclass')]
        iterable $rewindableGenerator,
        protected EnqueueMethod $enqueueMethod,
        protected PageRepository $pageRepository,
        protected BlockRepository $blockRepository
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
    }

    public function getAll(): array
    {
        $data = [];
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            $type        = $row->getType();
            $name        = $row->getName();
            $data[$name] = $type;
        }

        return $data;
    }

    public function getClass(
        Block $block
    ): ?BlockInterface
    {
        $type        = $block->getType();
        $entityBlock = $this->getEntity($block);
        if (!$entityBlock instanceof EntityBlockInterface || is_null($type)) {
            return null;
        }

        $class = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($type === $row->getType()) {
                $class = $row;

                break;
            }
        }

        return $class;
    }

    public function getClassCSS(
        array $dataClass,
        Block $block
    ): array
    {
        $type        = $block->getType();
        $entityBlock = $this->getEntity($block);
        if (!$entityBlock instanceof EntityBlockInterface || is_null($type)) {
            return $dataClass;
        }

        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($type === $row->getType()) {
                $dataClass = $row->getClassCSS($dataClass, $entityBlock);
            }
        }

        return $dataClass;
    }

    public function getContext(
        Block $block,
        ?EntityFrontInterface $entityFront
    ): mixed
    {
        $type        = $block->getType();
        $entityBlock = $this->getEntity($block);
        if (!$entityBlock instanceof EntityBlockInterface || is_null($type)) {
            return null;
        }

        $context = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($type === $row->getType()) {
                $context = $row->context($entityBlock, $entityFront);

                break;
            }
        }

        return $context;
    }

    public function getCustomBlock(): array
    {
        $blocks = $this->blockRepository->findBy(
            ['type' => 'custom']
        );

        $data = [];
        foreach ($blocks as $block) {
            /** @var Block $block */
            $title        = $block->getTitle();
            $id           = $block->getId();
            $data[$title] = $id;
        }

        return $data;
    }

    public function getEntity(Block $block): ?EntityBlockInterface
    {
        $entity = null;
        $field  = $this->getEntityField($block);
        if (is_null($field)) {
            return $entity;
        }

        $reflectionClass  = new ReflectionClass($block);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getName() === $field) {
                $entities = $propertyAccessor->getValue($block, $field);
                if (!$entities instanceof PersistentCollection || !$entities->offsetExists(0)) {
                    continue;
                }

                $entity = $entities->offsetGet(0);

                break;
            }
        }

        if (!$entity instanceof EntityBlockInterface) {
            $entity = null;
        }

        return $entity;
    }

    public function getEntityField(Block $block): ?string
    {
        $field       = null;
        $childentity = $this->getTypeEntity($block);
        if (is_null($childentity)) {
            return $field;
        }

        $childentity = new $childentity();

        $reflectionClass = new ReflectionClass($childentity);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ('block' == $reflectionProperty->getName()) {
                $attributes = $reflectionProperty->getAttributes();
                $attribute  = $attributes[0];
                $arguments  = $attribute->getArguments();
                $field      = $arguments['inversedBy'] ?? $field;

                break;
            }
        }

        return $field;
    }

    public function getName(Block $block): ?string
    {
        $type = $block->getType();
        if (is_null($type)) {
            return null;
        }

        $form = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($row->getType() === $type) {
                $form = $row->getName();

                break;
            }
        }

        return $form;
    }

    /**
     * @return array<string, string>
     */
    public function getRegions(): array
    {
        return [
            'header'  => 'header',
            'content' => 'content',
            'footer'  => 'footer',
        ];
    }

    public function getTwigTemplate(
        Block $block,
        ?EntityFrontInterface $entityFront
    ): ?string
    {
        $type        = $block->getType();
        $entityBlock = $this->getEntity($block);
        if (!$entityBlock instanceof EntityBlockInterface || is_null($type)) {
            return null;
        }

        $template = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($type === $row->getType()) {
                $template = $row->twig($entityBlock, $entityFront);

                break;
            }
        }

        return $template;
    }

    public function getTypeEntity(Block $block): ?string
    {
        $type = $block->getType();
        if (is_null($type)) {
            return null;
        }

        $block = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($row->getType() === $type) {
                $block = $row->getEntity();

                break;
            }
        }

        return $block;
    }

    public function getTypeForm(Block $block): ?string
    {
        $type = $block->getType();
        if (is_null($type)) {
            return null;
        }

        $form = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($row->getType() === $type) {
                $form = $row->getForm();

                break;
            }
        }

        return $form;
    }

    public function isShow(Block $block): bool
    {
        $type = $block->getType();
        if (is_null($type)) {
            return false;
        }

        $show = false;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($row->getType() === $type) {
                $show = $row->isShowForm();

                break;
            }
        }

        return $show;
    }

    public function process(string $region, int $position, array $notinpages): void
    {
        $block = $this->blockRepository->findOneBy(
            [
                'region'   => $region,
                'position' => $position,
            ]
        );
        if (!$block instanceof Block) {
            return;
        }

        $pages = $this->pageRepository->getBySlugs($notinpages);
        if (0 != (is_countable($pages) ? count($pages) : 0)) {
            foreach ($pages as $page) {
                $block->addNotinpage($page);
            }
        }

        $this->blockRepository->save($block);
    }

    public function setEntity(Block $block, EntityBlockInterface $entityBlock): void
    {
        $field = $this->getEntityField($block);
        if (is_null($field)) {
            return;
        }

        $reflectionClass  = new ReflectionClass($block);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getName() === $field) {
                $propertyAccessor->setValue($block, $field, [$entityBlock]);
            }
        }
    }

    public function showTemplate(
        Block $block,
        ?EntityFrontInterface $entityFront
    ): ?array
    {
        $type        = $block->getType();
        $entityBlock = $this->getEntity($block);
        if (!$entityBlock instanceof EntityBlockInterface || is_null($type)) {
            return null;
        }

        $template = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var BlockInterface $row */
            if ($type === $row->getType()) {
                $template = $row->template($entityBlock, $entityFront);
            }
        }

        return $template;
    }
}
