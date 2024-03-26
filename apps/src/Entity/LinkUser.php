<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\UserDataInterface;
use Labstag\Repository\LinkUserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: LinkUserRepository::class)]
class LinkUser extends Link implements UserDataInterface
{

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'linkUsers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refuser_id', nullable: false)]
    private ?UserInterface $user = null;

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
