<?php

namespace Labstag\Service;

use Labstag\Lib\RepositoryLib;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class RepositoryService
{

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('repositories')]
        iterable $rewindableGenerator
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
    }

    public function get(string $entity): ?RepositoryLib
    {
        $return = null;
        foreach ($this->rewindableGenerator as $repository) {
            /** @var RepositoryLib $repository */
            if ($repository->getClassName() == $entity) {
                $return = $repository;

                break;
            }
        }

        return $return;
    }
}
