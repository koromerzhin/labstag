<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\RouteUserRepository;

/**
 * @ORM\Entity(repositoryClass=RouteUserRepository::class)
 */
class RouteUser
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="users")
     */
    protected $refroute;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="routes")
     */
    protected $refuser;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $state;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefroute(): ?Route
    {
        return $this->refroute;
    }

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setRefroute(?Route $refroute): self
    {
        $this->refroute = $refroute;

        return $this;
    }

    public function setRefuser(?User $refuser): self
    {
        $this->refuser = $refuser;

        return $this;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }
}
