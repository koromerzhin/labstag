<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Labstag\Repository\OauthConnectUserRepository")
 */
class OauthConnectUser
{

    /**
     * @ORM\Column(type="array")
     *
     * @var array
     */
    protected $data = [];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $identity;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="oauthConnectUsers")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var User
     */
    protected $refuser;

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

    public function getRefuser(): ?User
    {
        return $this->refuser;
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

    public function setRefuser(?User $refuser): self
    {
        $this->refuser = $refuser;

        return $this;
    }
}
