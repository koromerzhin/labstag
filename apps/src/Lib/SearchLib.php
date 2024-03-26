<?php

namespace Labstag\Lib;

use DateTime;
use Labstag\Interfaces\EntityInterface;
use Labstag\Service\RepositoryService;
use ReflectionClass;
use ReflectionNamedType;

abstract class SearchLib
{

    public int $limit = 10;

    public int $page = 0;

    public function search(
        array $get,
        RepositoryService $repositoryService
    ): void
    {
        foreach ($get as $key => $value) {
            if (!property_exists(static::class, $key) || '' == $value) {
                continue;
            }

            $type = $this->getType($key);
            if (!is_null($type)) {
                if (DateTime::class == $type) {
                    $value = $this->initDateTime($value);
                } elseif (0 != substr_count((string) $type, 'Labstag')) {
                    $value = $this->initObject($value, $type, $repositoryService);
                }

                $this->{$key} = $value;
            }
        }
    }

    private function getType(string $key): ?string
    {
        $reflectionClass    = new ReflectionClass(static::class);
        $reflectionProperty = $reflectionClass->getProperty($key);
        $reflectionType     = $reflectionProperty->getType();

        if (!$reflectionType instanceof ReflectionNamedType) {
            return null;
        }

        return $reflectionType->getName();
    }

    private function initDateTime(mixed $value): ?DateTime
    {
        if (empty($value)) {
            return null;
        }

        $dateTime = new DateTime();
        [
            $year,
            $month,
            $day,
        ] = explode('-', (string) $value);
        $dateTime->setDate((int) $year, (int) $month, (int) $day);

        return $dateTime;
    }

    private function initObject(
        mixed $value,
        string $type,
        RepositoryService $repositoryService
    ): mixed
    {
        $object = new $type();
        if (!$object instanceof EntityInterface) {
            return $value;
        }

        $repositoryLib = $repositoryService->get($object::class);
        if (!$repositoryLib instanceof EntityInterface) {
            return $value;
        }

        return $repositoryLib->find($value);
    }
}
