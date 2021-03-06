<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Workflow;
use Labstag\Lib\ServiceEntityRepositoryLib;

class WorkflowRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workflow::class);
    }

    public function toDeleteEntities(array $entities)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
            'u.entity NOT IN (:entities)'
        );
        $query->setParameters(
            ['entities' => $entities]
        );

        return $query->getQuery()->getResult();
    }

    public function toDeletetransition(string $entity, array $transitions)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
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
