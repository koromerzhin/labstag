<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Post;
use Labstag\Lib\ServiceEntityRepositoryLib;

class PostRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findDateArchive()
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->select(
            'date_format(u.published,\'%Y-%m\') as code, u.published, COUNT(u)'
        );
        $query        = $queryBuilder->where(
            'u.state LIKE :state'
        );
        $query->orderBy('u.published', 'DESC');
        $query->groupBy('code');
        $query->orderBy('code', 'DESC');
        $query->setParameters(
            ['state' => '%publie%']
        );

        return $query->getQuery()->getResult();
    }

    public function findPublier()
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
            'u.state LIKE :state'
        );
        $query->orderBy('u.published', 'DESC');
        $query->setParameters(
            ['state' => '%publie%']
        );

        return $query->getQuery();
    }

    public function findPublierArchive($published)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where('u.state LIKE :state');
        $query->andWhere('date_format(u.published,\'%Y-%m\') = :published');
        $query->orderBy('u.published', 'DESC');
        $query->setParameters(
            [
                'state'     => '%publie%',
                'published' => $published,
            ]
        );

        return $query->getQuery();
    }

    public function findPublierUsername($username)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $query        = $queryBuilder->leftJoin('p.refuser', 'u');
        $query        = $query->where('p.state LIKE :state');
        $query->andWhere('u.username = :username');
        $query->orderBy('p.published', 'DESC');
        $query->setParameters(
            [
                'state'    => '%publie%',
                'username' => $username,
            ]
        );

        return $query->getQuery();
    }
}
