<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\EmailUserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: EmailUserRepository::class)]
class EmailUser extends Email implements EntityInterface
{

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'emailUsers', cascade: ['persist'])]
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
