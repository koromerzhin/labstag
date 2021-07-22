<?php

namespace Labstag\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Repository\PostRepository;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Uploadable()
 */
class Post
{
    use SoftDeleteableEntity;

    /**
     * @UploadableField(filename="img", path="post/img", slug="title")
     */
    protected $file;

    /**
     * @ORM\Column(type="array")
     */
    protected $state;

    /**
     * @ORM\Column(type="boolean")
     */
    private $commentaire;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Attachment::class, inversedBy="posts")
     */
    private $img;

    /**
     * @ORM\ManyToMany(targetEntity=Libelle::class, mappedBy="posts", cascade={"persist"})
     */
    private $libelles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="datetime")
     */
    private $published;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     */
    private $refuser;

    /**
     * @Gedmo\Slug(updatable=false, fields={"title"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="state_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"state"})
     */
    private $stateChanged;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    private $title;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    public function __construct()
    {
        $this->libelles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function addLibelle(Libelle $libelle): self
    {
        if (!$this->libelles->contains($libelle)) {
            $this->libelles[] = $libelle;
            $libelle->addPost($this);
        }

        return $this;
    }

    public function getCommentaire(): ?bool
    {
        return $this->commentaire;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getImg(): ?Attachment
    {
        return $this->img;
    }

    /**
     * @return Collection|Libelle[]
     */
    public function getLibelles(): Collection
    {
        return $this->libelles;
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

    public function getSlug(): ?string
    {
        return $this->slug;
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

    public function getUpdated()
    {
        return $this->updated;
    }

    public function removeLibelle(Libelle $libelle): self
    {
        if ($this->libelles->removeElement($libelle)) {
            $libelle->removePost($this);
        }

        return $this;
    }

    public function setCommentaire(bool $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
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

    public function setImg(?Attachment $img): self
    {
        $this->img = $img;

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

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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
