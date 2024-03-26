<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Category;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_category_trash')]
class CategoryRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Category::class);
    }

    public function findAllParentForAdmin(array $get): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.category IS NULL');

        return $this->setQuery($queryBuilder, $get);
    }

    public function findByBookmark(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->leftJoin('a.bookmarks', 'b');
        $queryBuilder->where('b.state LIKE :state');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByPost(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->leftJoin('a.posts', 'p');
        $queryBuilder->innerJoin('p.user', 'u');
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

    public function findTrashParentForAdmin(array $get): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where('a.deletedAt IS NOT NULL');
        $queryBuilder->andwhere('a.category IS NULL');

        return $this->setQuery($queryBuilder, $get);
    }
}
