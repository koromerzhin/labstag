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
        $query = $this->createQueryBuilder('u');
        $query->where(
            'u.entity NOT IN (:entities)'
        );
        $query->setParameters(
            ['entities' => $entities]
        );

        return $query->getQuery()->getResult();
    }

    public function toDeletetransition(string $entity, array $transitions): mixed
    {
        $query = $this->createQueryBuilder('u');
        $query->where(
            'u.entity=:entity AND u.transition NOT IN (:transitions)'
        );
        $query->setParameters(
            [
                'entity'      => $entity,
                'transitions' => $transitions,
            ]
        );

        return $query->getQuery()->getResult();
    }
}
