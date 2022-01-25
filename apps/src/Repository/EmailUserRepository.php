<?php

namespace Labstag\Repository;

use Doctrine\ORM\QueryBuilder;
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

    public function findAllForAdmin(array $get): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a');

        return $this->setQuery($queryBuilder, $get);
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

    protected function setQuery(QueryBuilder $query, array $get): QueryBuilder
    {
        $this->setQueryEtape($query, $get);
        $this->setQueryRefUser($query, $get);

        return $query;
    }
}
