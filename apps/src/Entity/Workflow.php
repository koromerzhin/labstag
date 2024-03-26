<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\WorkflowRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: WorkflowRepository::class)]
class Workflow implements EntityInterface
{

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $entity = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $transition = null;

    #[ORM\OneToMany(
        targetEntity: WorkflowGroupe::class,
        mappedBy: 'workflow',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $workflowGroupes;

    #[ORM\OneToMany(
        targetEntity: WorkflowUser::class,
        mappedBy: 'workflow',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $workflowUsers;

    public function __construct()
    {
        $this->workflowGroupes = new ArrayCollection();
        $this->workflowUsers   = new ArrayCollection();
    }

    public function addWorkflowGroupe(WorkflowGroupe $workflowGroupe): self
    {
        if (!$this->workflowGroupes->contains($workflowGroupe)) {
            $this->workflowGroupes[] = $workflowGroupe;
            $workflowGroupe->setRefworkflow($this);
        }

        return $this;
    }

    public function addWorkflowUser(WorkflowUser $workflowUser): self
    {
        if (!$this->workflowUsers->contains($workflowUser)) {
            $this->workflowUsers[] = $workflowUser;
            $workflowUser->setRefworkflow($this);
        }

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTransition(): ?string
    {
        return $this->transition;
    }

    public function getWorkflowGroupes(): Collection
    {
        return $this->workflowGroupes;
    }

    public function getWorkflowUsers(): Collection
    {
        return $this->workflowUsers;
    }

    public function removeWorkflowGroupe(WorkflowGroupe $workflowGroupe): self
    {
        $this->removeElementWorkflow(
            element: $this->workflowGroupes,
            workflowGroupe: $workflowGroupe
        );

        return $this;
    }

    public function removeWorkflowUser(WorkflowUser $workflowUser): self
    {
        $this->removeElementWorkflow(
            element: $this->workflowUsers,
            workflowUser: $workflowUser
        );

        return $this;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function setTransition(string $transition): self
    {
        $this->transition = $transition;

        return $this;
    }

    private function removeElementWorkflow(
        Collection $element,
        ?WorkflowGroupe $workflowGroupe = null,
        ?WorkflowUser $workflowUser = null
    ): void
    {
        $variable = is_null($workflowGroupe) ? $workflowUser : $workflowGroupe;
        if (!is_null($variable) && $element->removeElement($variable) && $variable->getRefworkflow() === $this) {
            $variable->setRefworkflow(null);
        }
    }
}
