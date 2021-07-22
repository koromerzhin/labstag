<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Repository\EditoRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EditoRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Uploadable()
 */
class Edito
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    protected $content;

    /**
     * @UploadableField(filename="fond", path="edito/fond", slug="title")
     */
    protected $file;

    /**
     * @ORM\ManyToOne(targetEntity=Attachment::class, inversedBy="editos")
     */
    protected $fond;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="editos")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $refuser;

    /**
     * @ORM\Column(type="array")
     */
    protected $state;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     * @Assert\NotBlank
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="datetime")
     */
    private $published;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="state_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"state"})
     */
    private $stateChanged;

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getFond(): ?Attachment
    {
        return $this->fond;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function getRefuser(): ?User
    {
        return $this->refuser;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getStateChanged()
    {
        return $this->stateChanged;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setFond(?Attachment $fond): self
    {
        $this->fond = $fond;

        return $this;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function setPublished(DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function setRefuser(?User $refuser): self
    {
        $this->refuser = $refuser;

        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
