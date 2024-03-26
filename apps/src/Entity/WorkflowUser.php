<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\WorkflowUserRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: WorkflowUserRepository::class)]
class WorkflowUser implements EntityInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $state = null;

    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'workflowUsers',
        cascade: ['persist']
    )
    ]
    #[ORM\JoinColumn(name: 'refuser_id')]
    private ?UserInterface $user = null;

    #[ORM\ManyToOne(
        targetEntity: Workflow::class,
        inversedBy: 'workflowUsers',
        cascade: ['persist']
    )
    ]
    #[ORM\JoinColumn(name: 'refworkflow_id')]
    private ?Workflow $workflow = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function getRefworkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function getState(): ?bool
    {
        return $this->state;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->user = $user;

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
