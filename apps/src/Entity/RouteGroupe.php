<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\RouteGroupeRepository;

/**
 * @ORM\Entity(repositoryClass=RouteGroupeRepository::class)
 */
class RouteGroupe
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="routes")
     */
    protected $refgroupe;

    /**
     * @ORM\ManyToOne(targetEntity=Route::class, inversedBy="groupes")
     */
    protected $refroute;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $state;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefgroupe(): ?Groupe
    {
        return $this->refgroupe;
    }

    public function getRefroute(): ?Route
    {
        return $this->refroute;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setRefgroupe(?Groupe $refgroupe): self
    {
        $this->refgroupe = $refgroupe;

        return $this;
    }

    public function setRefroute(?Route $refroute): self
    {
        $this->refroute = $refroute;

        return $this;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }
}
