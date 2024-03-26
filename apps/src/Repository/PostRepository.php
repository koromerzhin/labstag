<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Post;
use Labstag\Lib\RepositoryLib;

#[Trashable(url: 'admin_post_trash')]
class PostRepository extends RepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Post::class);
    }

    public function findDateArchive(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select(
            "date_format(p.published,'%Y-%m') as code, p.published, COUNT(p)"
        );
        $queryBuilder->where(
            'p.state LIKE :state'
        );
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->groupBy('code');
        $queryBuilder->orderBy('code', 'DESC');
        $queryBuilder->setParameters(
            ['state' => '%publie%']
        );

        return $queryBuilder->getQuery()->getResult();
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

    public function findPublierArchive(int $published): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->innerjoin('p.user', 'u');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->andWhere("date_format(p.published,'%Y-%m') = :published");
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->setParameters(
            [
                'state'     => '%publie%',
                'published' => $published,
            ]
        );

        return $queryBuilder->getQuery();
    }

    public function findPublierCategory(string $code): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->leftJoin('p.category', 'c');
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
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->leftJoin('p.libelles', 'l');
        $queryBuilder->andWhere('l.slug=:slug');
        $queryBuilder->setParameters(
            [
                'slug'  => $code,
                'state' => '%publie%',
            ]
        );

        return $queryBuilder->getQuery();
    }

    public function findPublierUsername(string $username): Query
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin('p.user', 'u');
        $queryBuilder->where('p.state LIKE :state');
        $queryBuilder->andWhere('u.username = :username');
        $queryBuilder->orderBy('p.published', 'DESC');
        $queryBuilder->setParameters(
            [
                'state'    => '%publie%',
                'username' => $username,
            ]
        );

        return $queryBuilder->getQuery();
    }
}
