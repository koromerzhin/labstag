<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\RouteRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 */
class Route implements Stringable
{

    /**
     * @ORM\OneToMany(targetEntity=RouteGroupe::class, mappedBy="refroute", orphanRemoval=true)
     */
    protected $groupes;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="guid", unique=true)
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity=RouteUser::class, mappedBy="refroute", orphanRemoval=true)
     */
    protected $users;

    public function __construct()
    {
        $this->groupes = new ArrayCollection();
        $this->users   = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function addGroupe(RouteGroupe $routeGroupe): self
    {
        if (!$this->groupes->contains($routeGroupe)) {
            $this->groupes[] = $routeGroupe;
            $routeGroupe->setRefroute($this);
        }

        return $this;
    }

    public function addUser(RouteUser $routeUser): self
    {
        if (!$this->users->contains($routeUser)) {
            $this->users[] = $routeUser;
            $routeUser->setRefroute($this);
        }

        return $this;
    }

    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function removeGroupe(RouteGroupe $routeGroupe): self
    {
        // set the owning side to null (unless already changed)
        if ($this->groupes->removeElement($routeGroupe) && $routeGroupe->getRefroute() === $this) {
            $routeGroupe->setRefroute(null);
        }

        return $this;
    }

    public function removeUser(RouteUser $routeUser): self
    {
        // set the owning side to null (unless already changed)
        if ($this->users->removeElement($routeUser) && $routeUser->getRefroute() === $this) {
            $routeUser->setRefroute(null);
        }

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
