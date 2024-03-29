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
        $query = $this->createQueryBuilder('p');
        $query->innerjoin('p.user', 'u');
        $query->where(
            'p.state LIKE :state'
        );
        $query->orderBy('p.published', 'DESC');
        $query->setParameters(
            ['state' => '%publie%']
        );

        return $query->getQuery();
    }

    public function findPublierCategory(string $code): Query
    {
        $query = $this->createQueryBuilder('b');
        $query->where('b.state LIKE :state');
        $query->orderBy('b.published', 'DESC');
        $query->leftJoin('b.category', 'c');
        $query->andWhere('c.slug=:slug');
        $query->setParameters(
            [
                'slug'  => $code,
                'state' => '%publie%',
            ]
        );

        return $query->getQuery();
    }

    public function findPublierLibelle(string $code): Query
    {
        $query = $this->createQueryBuilder('b');
        $query->where('b.state LIKE :state');
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
}
