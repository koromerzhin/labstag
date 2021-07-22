<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\GroupeRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=GroupeRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Groupe
{
    use SoftDeleteableEntity;

    /**
     * @Gedmo\Slug(updatable=false, fields={"name"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $code;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity=RouteGroupe::class, mappedBy="refgroupe")
     */
    protected $routes;

    /**
     * @ORM\OneToMany(
     *  targetEntity=User::class,
     *  mappedBy="refgroupe",
     *  cascade={"persist"}
     * )
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity=WorkflowGroupe::class, mappedBy="refgroupe")
     */
    private $workflowGroupes;

    public function __construct()
    {
        $this->routes          = new ArrayCollection();
        $this->workflowGroupes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function addRoute(RouteGroupe $route): self
    {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
            $route->setRefgroupe($this);
        }

        return $this;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setRefgroupe($this);
        }

        return $this;
    }

    public function addWorkflowGroupe(WorkflowGroupe $workflowGroupe): self
    {
        if (!$this->workflowGroupes->contains($workflowGroupe)) {
            $this->workflowGroupes[] = $workflowGroupe;
            $workflowGroupe->setRefgroupe($this);
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
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
     * @return Collection|RouteGroupe[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return Collection|WorkflowGroupe[]
     */
    public function getWorkflowGroupes(): Collection
    {
        return $this->workflowGroupes;
    }

    public function removeRoute(RouteGroupe $route): self
    {
        if ($this->routes->removeElement($route)) {
            // set the owning side to null (unless already changed)
            if ($route->getRefgroupe() === $this) {
                $route->setRefgroupe(null);
            }
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getRefgroupe() === $this) {
                $user->setRefgroupe(null);
            }
        }

        return $this;
    }

    public function removeWorkflowGroupe(WorkflowGroupe $workflowGroupe): self
    {
        if ($this->workflowGroupes->removeElement($workflowGroupe)) {
            // set the owning side to null (unless already changed)
            if ($workflowGroupe->getRefgroupe() === $this) {
                $workflowGroupe->setRefgroupe(null);
            }
        }

        return $this;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
