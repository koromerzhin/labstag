<?php

namespace Labstag\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\BookmarkRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Uploadable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: BookmarkRepository::class)]
class Bookmark implements EntityTrashInterface
{
    use SoftDeleteableEntity;

    #[ORM\ManyToOne(targetEntity: Attachment::class, inversedBy: 'bookmarks', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'img_id')]
    private ?Attachment $attachment = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'bookmarks', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refcategory_id')]
    private ?Category $category = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[UploadableField(filename: 'img', path: 'bookmark/img', slug: 'name')]
    private mixed $file;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $icon = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\ManyToMany(targetEntity: Libelle::class, inversedBy: 'bookmarks', cascade: ['persist'])]
    private Collection $libelles;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $published = null;

    #[Gedmo\Slug(updatable: false, fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: 'array')]
    private mixed $state;

    #[Gedmo\Timestampable(on: 'change', field: ['state'])]
    #[ORM\Column(name: 'state_changed', type: 'datetime', nullable: true)]
    private ?DateTimeInterface $stateChanged = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookmarks', cascade: ['persist'])]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(name: 'refuser_id', nullable: false)]
    private ?UserInterface $user = null;

    public function __construct()
    {
        $this->libelles = new ArrayCollection();
    }

    public function addLibelle(Libelle $libelle): self
    {
        if (!$this->libelles->contains($libelle)) {
            $this->libelles[] = $libelle;
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getFile(): mixed
    {
        return $this->file;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImg(): ?Attachment
    {
        return $this->attachment;
    }

    public function getLibelles(): Collection
    {
        return $this->libelles;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function getRefcategory(): ?Category
    {
        return $this->category;
    }

    public function getRefuser(): ?UserInterface
    {
        return $this->user;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getState(): mixed
    {
        return $this->state;
    }

    public function getStateChanged(): ?DateTimeInterface
    {
        return $this->stateChanged;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function removeLibelle(Libelle $libelle): self
    {
        $this->libelles->removeElement($libelle);

        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setFile(mixed $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function setImg(?Attachment $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setPublished(DateTimeInterface $dateTime): self
    {
        $this->published = $dateTime;

        return $this;
    }

    public function setRefcategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setRefuser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setState(mixed $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function setStateChanged(?DateTimeInterface $dateTime): self
    {
        $this->stateChanged = $dateTime;

        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
