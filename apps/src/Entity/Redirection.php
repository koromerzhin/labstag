<?php

namespace Labstag\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\RedirectionRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: RedirectionRepository::class)]
class Redirection implements EntityInterface
{

    #[ORM\Column]
    private ?int $action_code = null;

    #[ORM\Column]
    private ?int $action_type = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private DateTime $created;

    #[ORM\Column(type: Types::ARRAY)]
    private array $data = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $destination = null;

    #[ORM\Column]
    private bool $enable = false;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $last_count = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private ?bool $regex = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $source = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function getActionCode(): ?int
    {
        return $this->action_code;
    }

    public function getActionType(): ?int
    {
        return $this->action_type;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLastCount(): ?int
    {
        return $this->last_count;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function isRegex(): ?bool
    {
        return $this->regex;
    }

    public function setActionCode(int $action_code): static
    {
        $this->action_code = $action_code;

        return $this;
    }

    public function setActionType(int $action_type): static
    {
        $this->action_type = $action_type;

        return $this;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function setLastCount(int $last_count): static
    {
        $this->last_count = $last_count;

        return $this;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function setRegex(bool $regex): static
    {
        $this->regex = $regex;

        return $this;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
