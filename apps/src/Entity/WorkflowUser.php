<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\WorkflowUserRepository;

/**
 * @ORM\Entity(repositoryClass=WorkflowUserRepository::class)
 */
class WorkflowUser
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="workflowUsers")
     */
    private $refuser;

    /**
     * @ORM\ManyToOne(targetEntity=Workflow::class, inversedBy="workflowUsers")
     */
    private $refworkflow;

    /**
     * @ORM\Column(type="boolean")
     */
    private $state;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    public function getRefworkflow(): ?Workflow
    {
        return $this->refworkflow;
    }

    public function getState(): ?bool
    {
        return $this->state;
    }

    public function setRefuser(?User $refuser): self
    {
        $this->refuser = $refuser;

        return $this;
    }

    public function setRefworkflow(?Workflow $refworkflow): self
    {
        $this->refworkflow = $refworkflow;

        return $this;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }
}
