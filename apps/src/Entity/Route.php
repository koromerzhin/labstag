<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\RouteRepository;

/**
 * @ORM\Entity(repositoryClass=RouteRepository::class)
 */
class Route
{

    /**
     * @ORM\OneToMany(targetEntity=RouteGroupe::class, mappedBy="refroute")
     */
    protected $groupes;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity=RouteUser::class, mappedBy="refroute")
     */
    protected $users;

    public function __construct()
    {
        $this->groupes = new ArrayCollection();
        $this->users   = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function addGroupe(RouteGroupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
            $groupe->setRefroute($this);
        }

        return $this;
    }

    public function addUser(RouteUser $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setRefroute($this);
        }

        return $this;
    }

    /**
     * @return Collection|RouteGroupe[]
     */
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

    /**
     * @return Collection|RouteUser[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function removeGroupe(RouteGroupe $groupe): self
    {
        if ($this->groupes->removeElement($groupe)) {
            // set the owning side to null (unless already changed)
            if ($groupe->getRefroute() === $this) {
                $groupe->setRefroute(null);
            }
        }

        return $this;
    }

    public function removeUser(RouteUser $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getRefroute() === $this) {
                $user->setRefroute(null);
            }
        }

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
