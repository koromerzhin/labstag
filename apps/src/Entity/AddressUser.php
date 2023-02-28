<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\AddressUserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AddressUserRepository::class)]
class AddressUser extends Address
{

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'addressUsers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refuser_id')]
    protected ?UserInterface $refuser = null;

    public function getRefuser(): ?UserInterface
    {
        return $this->refuser;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->refuser = $user;

        return $this;
    }
}
