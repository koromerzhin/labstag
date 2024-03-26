<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\GeoCodeRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: GeoCodeRepository::class)]
class GeoCode implements EntityTrashInterface
{
    use SoftDeleteableEntity;

    #[ORM\Column(type: 'integer')]
    private int $accuracy;

    #[ORM\Column(type: 'string', length: 20)]
    private string $communityCode;

    #[ORM\Column(type: 'string', length: 100)]
    private string $communityName;

    #[ORM\Column(type: 'string', length: 2)]
    #[Assert\Country]
    private string $countryCode;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $latitude;

    #[ORM\Column(type: 'string', length: 255)]
    private string $longitude;

    #[ORM\Column(type: 'string', length: 180)]
    private string $placeName;

    #[ORM\Column(type: 'string', length: 20)]
    private string $postalCode;

    #[ORM\Column(type: 'string', length: 20)]
    private string $provinceCode;

    #[ORM\Column(type: 'string', length: 100)]
    private string $provinceName;

    #[ORM\Column(type: 'string', length: 20)]
    private string $stateCode;

    #[ORM\Column(type: 'string', length: 100)]
    private string $stateName;

    public function getAccuracy(): ?int
    {
        return $this->accuracy;
    }

    public function getCommunityCode(): ?string
    {
        return $this->communityCode;
    }

    public function getCommunityName(): ?string
    {
        return $this->communityName;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function getPlaceName(): ?string
    {
        return $this->placeName;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function getStateCode(): ?string
    {
        return $this->stateCode;
    }

    public function getStateName(): ?string
    {
        return $this->stateName;
    }

    public function setAccuracy(int $accuracy): self
    {
        $this->accuracy = $accuracy;

        return $this;
    }

    public function setCommunityCode(string $communityCode): self
    {
        $this->communityCode = $communityCode;

        return $this;
    }

    public function setCommunityName(string $communityName): self
    {
        $this->communityName = $communityName;

        return $this;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function setPlaceName(string $placeName): self
    {
        $this->placeName = $placeName;

        return $this;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function setProvinceCode(string $provinceCode): self
    {
        $this->provinceCode = $provinceCode;

        return $this;
    }

    public function setProvinceName(string $provinceName): self
    {
        $this->provinceName = $provinceName;

        return $this;
    }

    public function setStateCode(string $stateCode): self
    {
        $this->stateCode = $stateCode;

        return $this;
    }

    public function setStateName(string $stateName): self
    {
        $this->stateName = $stateName;

        return $this;
    }
}
