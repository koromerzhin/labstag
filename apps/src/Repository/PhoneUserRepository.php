<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\PhoneUser;

/**
 * @Trashable(url="admin_phoneuser_trash")
 */
class PhoneUserRepository extends PhoneRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhoneUser::class);
    }

    public function findAllForAdmin(): Query
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query        = $queryBuilder->leftJoin(
            'a.refuser',
            'u'
        );
        $query->where(
            'u.id IS NOT NULL'
        );

        return $query->getQuery();
    }

    public function findTrashForAdmin(): array
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query        = $queryBuilder->leftJoin(
            'a.refuser',
            'u'
        );
        $query->where(
            'u.deletedAt IS NOT NULL OR a.deletedAt IS NOT NULL'
        );

        return $query->getQuery()->getResult();
    }
}
