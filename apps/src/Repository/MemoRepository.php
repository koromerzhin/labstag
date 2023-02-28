<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Memo;
use Labstag\Lib\ServiceEntityRepositoryLib;

#[Trashable(url: 'admin_memo_trash')]
class MemoRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Memo::class);
    }

    public function findPublier(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('n');
        $queryBuilder->innerJoin('n.refuser', 'u');
        $queryBuilder->where(
            'n.state LIKE :state'
        );
        $queryBuilder->andWhere('n.dateStart >= now()');
        $queryBuilder->orderBy('n.dateStart', 'ASC');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );

        $queryBuilder->setMaxResults(1);

        return $queryBuilder->getQuery()->getResult();
    }
}
