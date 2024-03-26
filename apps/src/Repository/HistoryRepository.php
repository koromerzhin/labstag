<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\History;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_history_trash')]
class HistoryRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, History::class);
    }

    public function findPublier(): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->innerjoin('p.user', 'u');
        $queryBuilder->leftJoin('p.chapters', 'c');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->andWhere('c.state LIKE :state');
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );
        $queryBuilder->orderBy('p.published', 'DESC');

        return $queryBuilder->getQuery();
    }

    public function findPublierUsername(string $username): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin('p.user', 'u');
        $queryBuilder->leftJoin('p.chapters', 'c');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->andWhere('c.state LIKE :state');
        $queryBuilder->andWhere('u.username = :username');
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->setParameters(
            [
                'state'    => '%publie%',
                'username' => $username,
            ]
        );
        $queryBuilder->orderBy('p.published', 'DESC');

        return $queryBuilder->getQuery();
    }
}
