<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr",       type="string")
 * @ORM\DiscriminatorMap({"user":               "AddressUser"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
abstract class Address
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $city;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Country
     */
    protected $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $gps;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pmr;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $zipcode;

    public function __toString()
    {
        return implode(
            ' ',
            [
                $this->getStreet(),
                $this->getZipcode(),
                $this->getCity(),
                $this->getCountry(),
            ]
        );
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getGps(): ?string
    {
        return $this->gps;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function isPmr(): ?bool
    {
        return $this->pmr;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function setGps(string $gps): self
    {
        $this->gps = $gps;

        return $this;
    }

    public function setPmr(bool $pmr): self
    {
        $this->pmr = $pmr;

        return $this;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }
}