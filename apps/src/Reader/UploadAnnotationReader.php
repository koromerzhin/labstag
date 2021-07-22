<?php

namespace Labstag\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use ReflectionClass;

class UploadAnnotationReader
{

    protected AnnotationReader $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Liste les champs uploadable d'une entité (sous forme de tableau associatif).
     */
    public function getUploadableFields($entity): array
    {
        $properties = [];
        if (!$this->isUploadable($entity)) {
            return $properties;
        }

        $reflection = $this->setReflection($entity);
        foreach ($reflection->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, UploadableField::class);
            if (!is_null($annotation)) {
                $properties[$property->getName()] = $annotation;
            }
        }

        return $properties;
    }

    public function isUploadable($entity): bool
    {
        $reflection = $this->setReflection($entity);

        $annotation = $this->reader->getClassAnnotation($reflection, Uploadable::class);

        return !is_null($annotation);
    }

    protected function setReflection($entity): ReflectionClass
    {
        return new ReflectionClass(get_class($entity));
    }
}
