<?php

namespace Labstag\Service;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Lib\RepositoryLib;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TrashService
{

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('repositories')]
        iterable $rewindableGenerator,
        protected ManagerRegistry $managerRegistry,
        protected CsrfTokenManagerInterface $csrfTokenManager
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
    }

    public function all(): array
    {
        $data = [];
        foreach ($this->rewindableGenerator as $repository) {
            /** @var RepositoryLib $repository */
            $isTrashable = $this->isTrashable($repository);
            if (!$isTrashable) {
                continue;
            }

            $entity = $repository->getClassName();
            $trash  = $repository->findTrashForAdmin([]);
            $result = $trash->getQuery()->getResult();
            $test   = is_countable($result) ? count($result) : 0;
            if (0 == $test) {
                continue;
            }

            $data[] = [
                'name'       => strtolower(substr((string) $entity, strrpos((string) $entity, '\\') + 1)),
                'properties' => $this->getProperties($repository),
                'entity'     => substr((string) $entity, strrpos((string) $entity, '\\') + 1),
                'total'      => $test,
                'token'      => $this->csrfTokenManager->getToken('empty')->getValue(),
            ];
        }

        return $data;
    }

    protected function getProperties(RepositoryLib $serviceEntityRepositoryLib): array
    {
        $properties = [];
        if (!$this->isTrashable($serviceEntityRepositoryLib)) {
            return $properties;
        }

        $reflectionClass = new ReflectionClass($serviceEntityRepositoryLib);
        $attributes      = $reflectionClass->getAttributes();
        foreach ($attributes as $attribute) {
            if (Trashable::class != $attribute->getName()) {
                continue;
            }

            $properties = $attribute->getArguments();

            break;
        }

        return $properties;
    }

    private function isTrashable(RepositoryLib $serviceEntityRepositoryLib): bool
    {
        $reflectionClass = new ReflectionClass($serviceEntityRepositoryLib);
        $attributes      = $reflectionClass->getAttributes();
        $find            = false;
        foreach ($attributes as $attribute) {
            if (Trashable::class != $attribute->getName()) {
                continue;
            }

            $find = true;

            break;
        }

        return $find;
    }
}
