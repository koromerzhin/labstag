<?php

namespace Labstag\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\HttpErrorLogsRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: HttpErrorLogsRepository::class)]
class HttpErrorLogs implements EntityInterface
{

    #[ORM\Column(length: 255)]
    private ?string $agent = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private DateTime $created;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $domain = null;

    #[ORM\Column]
    private ?int $http_code = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $referer = null;

    #[ORM\Column]
    private array $request_data = [];

    #[ORM\Column(length: 255)]
    private ?string $request_method = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    private ?string $ip = null;

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getHttpCode(): ?int
    {
        return $this->http_code;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function getRequestData(): array
    {
        return $this->request_data;
    }

    public function getRequestMethod(): ?string
    {
        return $this->request_method;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setAgent(string $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function setHttpCode(int $http_code): static
    {
        $this->http_code = $http_code;

        return $this;
    }

    public function setReferer(?string $referer): static
    {
        $this->referer = $referer;

        return $this;
    }

    public function setRequestData(array $request_data): static
    {
        $this->request_data = $request_data;

        return $this;
    }

    public function setRequestMethod(string $request_method): static
    {
        $this->request_method = $request_method;

        return $this;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}
