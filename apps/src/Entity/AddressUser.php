<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\UserDataInterface;
use Labstag\Repository\AddressUserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AddressUserRepository::class)]
class AddressUser extends Address implements UserDataInterface
{

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'addressUsers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refuser_id')]
    protected ?UserInterface $user = null;

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }
}
