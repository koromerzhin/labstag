<?php

namespace Labstag\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"user": "EmailUser"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class Email
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Email(
     *     message="The email '{{ value }}' is not a valid email."
     * )
     */
    protected $adresse;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $principal;

    /**
     * @ORM\Column(type="array")
     */
    protected $state;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="state_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"state"})
     */
    private $stateChanged;

    public function __construct()
    {
        $this->principal = false;
    }

    public function __toString()
    {
        return $this->getAdresse();
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getStateChanged()
    {
        return $this->stateChanged;
    }

    public function isPrincipal(): ?bool
    {
        return $this->principal;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function setPrincipal(bool $principal): self
    {
        $this->principal = $principal;

        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;
    }
}
