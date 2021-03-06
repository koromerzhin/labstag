<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\LienUserRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LienUserRepository::class)
 */
class LienUser extends Lien
{

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="lienUsers")
     * @Assert\NotBlank
     */
    protected $refuser;

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    public function setRefuser(?User $refuser): self
    {
        $this->refuser = $refuser;

        return $this;
    }
}
