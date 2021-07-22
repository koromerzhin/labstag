<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\WorkflowRepository;

/**
 * @ORM\Entity(repositoryClass=WorkflowRepository::class)
 */
class Workflow
{

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $entity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $transition;

    /**
     * @ORM\OneToMany(targetEntity=WorkflowGroupe::class, mappedBy="refworkflow")
     */
    private $workflowGroupes;

    /**
     * @ORM\OneToMany(targetEntity=WorkflowUser::class, mappedBy="refworkflow")
     */
    private $workflowUsers;

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

    /**
     * @return Collection|WorkflowGroupe[]
     */
    public function getWorkflowGroupes(): Collection
    {
        return $this->workflowGroupes;
    }

    /**
     * @return Collection|WorkflowUser[]
     */
    public function getWorkflowUsers(): Collection
    {
        return $this->workflowUsers;
    }

    public function removeWorkflowGroupe(WorkflowGroupe $workflowGroupe): self
    {
        if ($this->workflowGroupes->removeElement($workflowGroupe)) {
            // set the owning side to null (unless already changed)
            if ($workflowGroupe->getRefworkflow() === $this) {
                $workflowGroupe->setRefworkflow(null);
            }
        }

        return $this;
    }

    public function removeWorkflowUser(WorkflowUser $workflowUser): self
    {
        if ($this->workflowUsers->removeElement($workflowUser)) {
            // set the owning side to null (unless already changed)
            if ($workflowUser->getRefworkflow() === $this) {
                $workflowUser->setRefworkflow(null);
            }
        }

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
}
