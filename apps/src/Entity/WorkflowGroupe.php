<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\WorkflowGroupeRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: WorkflowGroupeRepository::class)]
class WorkflowGroupe implements EntityInterface
{

    #[ORM\ManyToOne(targetEntity: Groupe::class, inversedBy: 'workflowGroupes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refgroupe_id')]
    private ?Groupe $groupe = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $state = null;

    #[ORM\ManyToOne(targetEntity: Workflow::class, inversedBy: 'workflowGroupes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refworkflow_id')]
    private ?Workflow $workflow = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRefgroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function getRefworkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function getState(): ?bool
    {
        return $this->state;
    }

    public function setRefgroupe(?Groupe $groupe): self
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function setRefworkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }
}
