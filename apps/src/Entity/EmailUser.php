<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\EmailUserRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EmailUserRepository::class)
 */
class EmailUser extends Email
{

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="emailUsers")
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
