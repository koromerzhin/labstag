<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Bookmark;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_bookmark_trash')]
class BookmarkRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Bookmark::class);
    }

    public function findPublier(): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->innerjoin('p.user', 'u');
        $queryBuilder->where(
            'p.state LIKE :state'
        );
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );

        return $queryBuilder->getQuery();
    }

    public function findPublierCategory(string $code): Query
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $queryBuilder->where('b.state LIKE :state');
        $queryBuilder->orderBy('b.published', 'DESC');
        $queryBuilder->leftJoin('b.category', 'c');
        $queryBuilder->andWhere('c.slug=:slug');
        $queryBuilder->setParameters(
            [
                'slug'  => $code,
                'state' => '%publie%',
            ]
        );

        return $queryBuilder->getQuery();
    }

    public function findPublierLibelle(string $code): Query
    {
        $queryBuilder = $this->createQueryBuilder('b');
        $queryBuilder->where('b.state LIKE :state');
        $queryBuilder->orderBy('b.published', 'DESC');
        $queryBuilder->leftJoin('b.libelles', 'l');
        $queryBuilder->andWhere('l.slug=:slug');
        $queryBuilder->setParameters(
            [
                'slug'  => $code,
                'state' => '%publie%',
            ]
        );

        return $queryBuilder->getQuery();
    }
}
