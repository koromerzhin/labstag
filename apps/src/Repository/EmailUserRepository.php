<?php

namespace Labstag\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\EmailUser;
use Labstag\Entity\User;

/**
 * @Trashable(url="admin_emailuser_trash")
 */
class EmailUserRepository extends EmailRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailUser::class);
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

    public function getEmailsUserVerif(User $user, bool $verif): array
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
            'u.refuser=:user AND u.state LIKE :state'
        );
        $query->setParameters(
            [
                'user'  => $user,
                'state' => $verif ? '%valide%' : '%averifier%',
            ]
        );

        return $query->getQuery()->getResult();
    }
}
