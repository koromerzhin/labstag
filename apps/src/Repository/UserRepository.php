<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Annotation\Trashable;
use Labstag\Entity\User;
use Labstag\Lib\ServiceEntityRepositoryLib;

/**
 * @Trashable(url="admin_user_trash")
 */
class UserRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserEnable(string $field): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
            'u.username=:username OR u.email=:email'
        );
        $query->andWhere('u.state LIKE :state1 OR u.state LIKE :state2');
        $query->setParameters(
            [
                'state1'   => '%valider%',
                'state2'   => '%lostpassword%',
                'username' => $field,
                'email'    => $field,
            ]
        );

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findUserName(string $field)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $query        = $queryBuilder->where(
            'u.username LIKE :username OR u.email LIKE :email'
        );
        $query->setParameters(
            [
                'username' => '%'.$field.'%',
                'email'    => '%'.$field.'%',
            ]
        );

        return $query->getQuery()->getResult();
    }
}
