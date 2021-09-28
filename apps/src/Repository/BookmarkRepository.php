<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Bookmark;
use Labstag\Lib\ServiceEntityRepositoryLib;

/**
 * @Trashable(url="admin_bookmark_trash")
 */
class BookmarkRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bookmark::class);
    }

    public function findPublierLibelle($code)
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $query        = $queryBuilder->where('b.state LIKE :state');
        $query->orderBy('b.published', 'DESC');
        $query->leftJoin('b.libelles', 'l');
        $query->andWhere('l.slug=:slug');
        $query->setParameters(
            [
                'slug'  => $code,
                'state' => '%publie%',
            ]
        );

        return $query->getQuery();
    }

    public function findPublierCategory($code)
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $query        = $queryBuilder->where('b.state LIKE :state');
        $query->orderBy('b.published', 'DESC');
        $query->leftJoin('b.refcategory', 'c');
        $query->andWhere('c.slug=:slug');
        $query->setParameters(
            [
                'slug'  => $code,
                'state' => '%publie%',
            ]
        );

        return $query->getQuery();
    }

    public function findPublier()
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $query        = $queryBuilder->innerjoin('p.refuser', 'u');
        $query->where(
            'p.state LIKE :state'
        );
        $query->orderBy('p.published', 'DESC');
        $query->setParameters(
            ['state' => '%publie%']
        );

        return $query->getQuery();
    }
}
