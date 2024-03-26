<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Workflow;
use Labstag\Lib\RepositoryLib;

class WorkflowRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Workflow::class);
    }

    public function toDeleteEntities(array $entities): mixed
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.entity NOT IN (:entities)'
        );
        $queryBuilder->setParameters(
            ['entities' => $entities]
        );

        return $queryBuilder->getQuery()->getResult();
    }

    public function toDeletetransition(string $entity, array $transitions): mixed
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.entity=:entity AND u.transition NOT IN (:transitions)'
        );
        $queryBuilder->setParameters(
            [
                'entity'      => $entity,
                'transitions' => $transitions,
            ]
        );

        return $queryBuilder->getQuery()->getResult();
    }
}
