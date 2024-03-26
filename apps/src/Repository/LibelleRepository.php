<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Libelle;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_libelle_trash')]
class LibelleRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Libelle::class);
    }

    public function findByBookmark(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->innerJoin('a.bookmarks', 'b');
        $queryBuilder->where('b.state LIKE :state');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByPost(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->innerJoin('a.posts', 'p');
        $queryBuilder->innerjoin('p.user', 'u');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );

        return $queryBuilder->getQuery()->getResult();
    }

    public function findName(string $field): mixed
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.name LIKE :name'
        );
        $queryBuilder->setParameters(
            [
                'name' => '%'.$field.'%',
            ]
        );

        return $queryBuilder->getQuery()->getResult();
    }
}
