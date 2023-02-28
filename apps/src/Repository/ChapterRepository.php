<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\Chapter;
use Labstag\Lib\ServiceEntityRepositoryLib;

#[Trashable(url: 'admin_history_trash')]
class ChapterRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Chapter::class);
    }

    public function findChapterByHistory(
        string $history,
        string $chapter
    ): mixed
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->leftJoin('c.refhistory', 'h');
        $queryBuilder->where('h.slug = :slughistory');
        $queryBuilder->andWhere('c.slug = :slugchapter');
        $queryBuilder->setParameters(
            [
                'slugchapter' => $chapter,
                'slughistory' => $history,
            ]
        );

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
