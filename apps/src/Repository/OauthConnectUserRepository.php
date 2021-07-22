<?php

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\User;
use Labstag\Lib\ServiceEntityRepositoryLib;

class OauthConnectUserRepository extends ServiceEntityRepositoryLib
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OauthConnectUser::class);
    }

    public function findDistinctAllOauth(): array
    {
        $builder = $this->createQueryBuilder('u');
        $builder->select('u.name');
        $builder->distinct();
        $builder->orderBy('u.name', 'ASC');

        return $builder->getQuery()->getResult();
    }

    /**
     * @return OauthConnectUser|void
     */
    public function findOauthNotUser(?User $user, ?string $identity, ?string $client)
    {
        if (is_null($identity) || is_null($user) || is_null($client)) {
            return;
        }

        $dql = $this->createQueryBuilder('p');
        $dql->where('p.refuser! = :iduser');
        $dql->andWhere('p.identity = :identity');
        $dql->andWhere('p.name = :name');
        $dql->setParameters(
            [
                'iduser'   => $user->getId(),
                'name'     => $client,
                'identity' => $identity,
            ]
        );

        return $dql->getQuery()->getOneOrNullResult();
    }

    /**
     * @return OauthConnectUser|void
     */
    public function findOneOauthByUser(?string $oauthCode, ?User $user)
    {
        if (is_null($oauthCode) || is_null($user)) {
            return;
        }

        $dql = $this->createQueryBuilder('p');
        $dql->where('p.name = :name');
        $dql->andWhere('p.refuser = :iduser');
        $dql->setParameters(
            [
                'iduser' => (string) $user->getId(),
                'name'   => $oauthCode,
            ]
        );

        return $dql->getQuery()->getOneOrNullResult();
    }

    /**
     * @return OauthConnectUser|void
     */
    public function login(?string $identity, ?string $oauth)
    {
        if (is_null($identity) || is_null($oauth)) {
            return;
        }

        $builder = $this->createQueryBuilder('u');
        $builder->where(
            'u.name = :name AND u.identity = :identity'
        );
        $builder->setParameters(
            [
                'name'     => $oauth,
                'identity' => $identity,
            ]
        );

        return $builder->getQuery()->getOneOrNullResult();
    }
}
