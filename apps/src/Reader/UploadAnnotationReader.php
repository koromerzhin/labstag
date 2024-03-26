<?php

namespace Labstag\Reader;

use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Interfaces\EntityInterface;
use ReflectionClass;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UploadAnnotationReader
{
    public function enableAttachment(array $annotations, array $fields): bool
    {
        $enable = false;
        foreach (array_keys($annotations) as $key) {
            if (array_key_exists($key, $fields)) {
                $enable = true;

                break;
            }

            foreach ($fields as $field) {
                $type = $field->getConfig()->getType()->getInnerType();
                if ($type instanceof CollectionType) {
                    foreach ($field->all() as $data) {
                        $enable = $this->enableAttachment($annotations, $data->all());
                        if ($enable) {
                            break;
                        }
                    }

                    if ($enable) {
                        break;
                    }
                }
            }
        }

        return $enable;
    }

    /**
     * Liste les champs uploadable d'une entité (sous forme de tableau associatif).
     *
     * @return mixed[]
     */
    public function getUploadableFields(EntityInterface $entity): array
    {
        $properties = [];
        if (!$this->isUploadable($entity)) {
            return $properties;
        }

        $reflectionClass = new ReflectionClass($entity::class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes();
            foreach ($attributes as $attribute) {
                if (UploadableField::class == $attribute->getName()) {
                    $properties[$reflectionProperty->getName()] = $attribute->newInstance();
                }
            }
        }

        return $properties;
    }

    private function isUploadable(EntityInterface $entity): bool
    {
        $reflectionClass = new ReflectionClass($entity::class);

        $uploadable = false;
        foreach ($reflectionClass->getAttributes() as $attribute) {
            if (Uploadable::class === $attribute->getName()) {
                $uploadable = true;

                break;
            }
        }

        return $uploadable;
    }
}
