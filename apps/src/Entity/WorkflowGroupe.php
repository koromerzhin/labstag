<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Repository\WorkflowGroupeRepository;

/**
 * @ORM\Entity(repositoryClass=WorkflowGroupeRepository::class)
 */
class WorkflowGroupe
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="workflowGroupes")
     */
    private $refgroupe;

    /**
     * @ORM\ManyToOne(targetEntity=Workflow::class, inversedBy="workflowGroupes")
     */
    private $refworkflow;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getRefgroupe(): ?Groupe
    {
        return $this->refgroupe;
    }

    public function setRefgroupe(?Groupe $refgroupe): self
    {
        $this->refgroupe = $refgroupe;

        return $this;
    }

    public function getRefworkflow(): ?Workflow
    {
        return $this->refworkflow;
    }

    public function setRefworkflow(?Workflow $refworkflow): self
    {
        $this->refworkflow = $refworkflow;

        return $this;
    }
}
