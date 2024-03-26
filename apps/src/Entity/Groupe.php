<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\GroupeRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: GroupeRepository::class)]
class Groupe implements Stringable, EntityTrashInterface
{
    use SoftDeleteableEntity;

    #[ORM\ManyToMany(targetEntity: Block::class, mappedBy: 'groupes')]
    private Collection $blocks;

    #[Gedmo\Slug(updatable: false, fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $code;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToMany(targetEntity: Layout::class, mappedBy: 'groupes')]
    private Collection $layouts;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\OneToMany(
        targetEntity: RouteGroupe::class,
        mappedBy: 'groupe',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $routes;

    #[ORM\OneToMany(
        targetEntity: User::class,
        mappedBy: 'refgroupe',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $users;

    #[ORM\OneToMany(
        targetEntity: WorkflowGroupe::class,
        mappedBy: 'groupe',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $workflowGroupes;

    public function __construct()
    {
        $this->routes          = new ArrayCollection();
        $this->workflowGroupes = new ArrayCollection();
        $this->users           = new ArrayCollection();
        $this->blocks          = new ArrayCollection();
        $this->layouts         = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function addBlock(Block $block): self
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->addGroupe($this);
        }

        return $this;
    }

    public function addLayout(Layout $layout): self
    {
        if (!$this->layouts->contains($layout)) {
            $this->layouts->add($layout);
            $layout->addGroupe($this);
        }

        return $this;
    }

    public function addRoute(RouteGroupe $routeGroupe): self
    {
        if (!$this->routes->contains($routeGroupe)) {
            $this->routes[] = $routeGroupe;
            $routeGroupe->setRefgroupe($this);
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

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Layout>
     */
    public function getLayouts(): Collection
    {
        return $this->layouts;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getWorkflowGroupes(): Collection
    {
        return $this->workflowGroupes;
    }

    public function removeBlock(Block $block): self
    {
        if ($this->blocks->removeElement($block)) {
            $block->removeGroupe($this);
        }

        return $this;
    }

    public function removeLayout(Layout $layout): self
    {
        if ($this->layouts->removeElement($layout)) {
            $layout->removeGroupe($this);
        }

        return $this;
    }

    public function removeRoute(RouteGroupe $routeGroupe): self
    {
        $this->removeElementGroupe(
            element: $this->routes,
            routeGroupe: $routeGroupe
        );

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
        $this->removeElementGroupe(
            element: $this->workflowGroupes,
            workflowGroupe: $workflowGroupe
        );

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

    private function removeElementGroupe(
        Collection $element,
        ?RouteGroupe $routeGroupe = null,
        ?WorkflowGroupe $workflowGroupe = null
    ): void
    {
        if (is_null($routeGroupe) && is_null($workflowGroupe)) {
            return;
        }

        $variable = is_null($routeGroupe) ? $workflowGroupe : $routeGroupe;
        if ($element->removeElement($variable) && $variable->getRefgroupe() === $this) {
            $variable->setRefgroupe(null);
        }
    }
}
