<?php

namespace Labstag\Service;

use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\PersistentCollection;
use Labstag\Entity\Paragraph;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Interfaces\EntityParagraphInterface;
use Labstag\Interfaces\ParagraphInterface;
use Labstag\Interfaces\PublicInterface;
use Labstag\Queue\EnqueueMethod;
use Labstag\Repository\ParagraphRepository;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Environment;

class ParagraphService
{

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('paragraphsclass')]
        iterable $rewindableGenerator,
        protected Environment $twigEnvironment,
        protected EnqueueMethod $enqueueMethod,
        protected ParagraphRepository $paragraphRepository
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
    }

    public function add(
        EntityFrontInterface $entityFront,
        string $code,
        ?array $config = []
    ): void
    {
        $position = (is_countable($entityFront->getParagraphs()) ? count($entityFront->getParagraphs()) : 0) + 1;

        $paragraph = new Paragraph();
        $paragraph->setType($code);
        $paragraph->setPosition($position);
        $paragraph = $this->setParent($paragraph, $entityFront);

        $this->paragraphRepository->save($paragraph);
        if (0 != count((array) $config)) {
            $this->enqueueMethod->async(
                static::class,
                'process',
                [
                    'paragraphId' => $paragraph->getId(),
                    'config'      => $config,
                ]
            );
        }
    }

    public function getAll(EntityFrontInterface $entityFront): array
    {
        $data = [];
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            $inUse = $row->useIn();
            $type  = $row->getType();
            $name  = $row->getName();
            if (in_array($entityFront::class, $inUse)) {
                $data[$name] = $type;
            }
        }

        return $data;
    }

    public function getClass(Paragraph $paragraph): ?ParagraphInterface
    {
        $type            = $paragraph->getType();
        $entityParagraph = $this->getEntity($paragraph);
        if (!$entityParagraph instanceof EntityParagraphInterface || is_null($type)) {
            return null;
        }

        $class = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($type === $row->getType()) {
                $class = $row;

                break;
            }
        }

        return $class;
    }

    public function getClassCSS(
        array $dataClass,
        Paragraph $paragraph
    ): array
    {
        $type            = $paragraph->getType();
        $entityParagraph = $this->getEntity($paragraph);
        if (is_null($entityParagraph)) {
            return $dataClass;
        }

        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($type == $row->getType()) {
                $dataClass = $row->getClassCSS($dataClass, $entityParagraph);

                break;
            }
        }

        return $dataClass;
    }

    public function getContext(Paragraph $paragraph): mixed
    {
        $type            = $paragraph->getType();
        $entityParagraph = $this->getEntity($paragraph);
        if (!$entityParagraph instanceof EntityParagraphInterface || is_null($type)) {
            return null;
        }

        $context = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($type === $row->getType()) {
                $context = $row->context($entityParagraph);

                break;
            }
        }

        return $context;
    }

    public function getEntity(Paragraph $paragraph): ?EntityParagraphInterface
    {
        $entity = null;
        $field  = $this->getEntityField($paragraph);
        if (is_null($field)) {
            return $entity;
        }

        $reflectionClass  = new ReflectionClass($paragraph);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getName() === $field) {
                $entities = $propertyAccessor->getValue($paragraph, $field);
                if (!$entities instanceof PersistentCollection || !$entities->offsetExists(0)) {
                    continue;
                }

                $entity = $entities->offsetGet(0);

                break;
            }
        }

        if (!$entity instanceof EntityParagraphInterface) {
            $entity = null;
        }

        return $entity;
    }

    public function getEntityField(Paragraph $paragraph): ?string
    {
        $field       = null;
        $childentity = $this->getTypeEntity($paragraph);
        if (!is_string($childentity)) {
            return $field;
        }

        $childentity = new $childentity();

        $reflectionClass = new ReflectionClass($childentity);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ('paragraph' == $reflectionProperty->getName()) {
                $attributes = $reflectionProperty->getAttributes();
                $attribute  = $attributes[0];
                $arguments  = $attribute->getArguments();
                $field      = $arguments['inversedBy'] ?? $field;

                break;
            }
        }

        return $field;
    }

    public function getName(Paragraph $paragraph): string
    {
        $type = $paragraph->getType();
        if (is_null($type)) {
            return '';
        }

        $name = '';
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($row->getType() === $type) {
                $name = $row->getName();

                break;
            }
        }

        return $name;
    }

    public function getNameByCode(string $code): string
    {
        $name = '';
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($row->getType() === $code) {
                $name = $row->getName();

                break;
            }
        }

        return $name;
    }

    public function getParent(Paragraph $paragraph): ?PublicInterface
    {
        $entity           = null;
        $reflectionClass  = new ReflectionClass($paragraph);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes();
            foreach ($attributes as $attribute) {
                if (ManyToOne::class == $attribute->getName()) {
                    $entity = $propertyAccessor->getValue(
                        $paragraph,
                        $reflectionProperty->getName()
                    );
                    if (!is_null($entity)) {
                        break;
                    }
                }
            }

            if (!is_null($entity)) {
                break;
            }
        }

        return $entity;
    }

    public function getTwigTemplate(Paragraph $paragraph): ?string
    {
        $type            = $paragraph->getType();
        $entityParagraph = $this->getEntity($paragraph);
        if (!$entityParagraph instanceof EntityParagraphInterface || is_null($type)) {
            return null;
        }

        $template = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($type === $row->getType()) {
                $template = $row->twig($entityParagraph);

                break;
            }
        }

        return $template;
    }

    public function getTypeEntity(Paragraph $paragraph): ?string
    {
        $type = $paragraph->getType();
        if (is_null($type)) {
            return null;
        }

        $paragraph = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($row->getType() === $type) {
                $paragraph = $row->getEntity();

                break;
            }
        }

        return $paragraph;
    }

    public function getTypeForm(Paragraph $paragraph): ?string
    {
        $type = $paragraph->getType();
        if (is_null($type)) {
            return null;
        }

        $form = null;
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($row->getType() === $type) {
                $form = $row->getForm();

                break;
            }
        }

        return $form;
    }

    public function isShow(Paragraph $paragraph): bool
    {
        $type = $paragraph->getType();
        if (is_null($type)) {
            return false;
        }

        $show = false;
        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($row->getType() === $type) {
                $show = $row->isShowForm();

                break;
            }
        }

        return $show;
    }

    public function process(string $paragraphId, array $config): void
    {
        $paragraph = $this->paragraphRepository->find($paragraphId);
        if (!$paragraph instanceof Paragraph) {
            return;
        }

        $entityParagraph = $this->getEntity($paragraph);
        $reflectionClass = new ReflectionClass($entityParagraph::class);
        foreach ($config as $key => $value) {
            $reflectionClass->getProperty($key)->setValue($entityParagraph, $value);
        }

        $this->paragraphRepository->save($entityParagraph);
    }

    public function setData(Paragraph $paragraph): void
    {
        $entityParagraph = $this->getEntity($paragraph);
        $type            = $paragraph->getType();
        if ($entityParagraph instanceof EntityParagraphInterface || is_null($type)) {
            return;
        }

        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($row->getType() === $type) {
                $row->setData($paragraph);

                break;
            }
        }
    }

    public function setParent(Paragraph $paragraph, ?EntityFrontInterface $entityFront): Paragraph
    {
        $find             = false;
        $reflectionClass  = new ReflectionClass($paragraph);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes();
            foreach ($attributes as $attribute) {
                $arguments = $attribute->getArguments();
                if (ManyToOne::class == $attribute->getName() && $arguments['targetEntity'] == $entityFront::class) {
                    $propertyAccessor->setValue(
                        $paragraph,
                        $reflectionProperty->getName(),
                        $entityFront
                    );
                    $find = true;

                    break;
                }
            }

            if ($find) {
                break;
            }
        }

        return $paragraph;
    }

    public function showTemplate(Paragraph $paragraph): ?array
    {
        $type            = $paragraph->getType();
        $entityParagraph = $this->getEntity($paragraph);
        $template        = null;
        if (is_null($entityParagraph)) {
            return $template;
        }

        foreach ($this->rewindableGenerator as $row) {
            /** @var ParagraphInterface $row */
            if ($type == $row->getType()) {
                $template = $row->template($entityParagraph);

                break;
            }
        }

        return $template;
    }
}
