<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\UserDataInterface;
use Labstag\Repository\OauthConnectUserRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: OauthConnectUserRepository::class)]
class OauthConnectUser implements UserDataInterface
{

    #[ORM\Column(type: 'array')]
    protected array $data = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected string $identity;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $name;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'oauthConnectUsers', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refuser_id', nullable: true)]
    protected ?UserInterface $user = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }
}
