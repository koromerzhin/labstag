<?php

namespace Labstag\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Repository\AttachmentRepository;

/**
 * @ORM\Entity(repositoryClass=AttachmentRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Attachment
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $dimensions = [];

    /**
     * @ORM\OneToMany(targetEntity=Edito::class, mappedBy="fond")
     */
    protected $editos;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity=NoteInterne::class, mappedBy="fond")
     */
    protected $noteInternes;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="img")
     */
    protected $posts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $size;

    /**
     * @ORM\Column(type="array")
     */
    protected $state;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="avatar")
     */
    protected $users;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="state_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"state"})
     */
    private $stateChanged;

    public function __construct()
    {
        $this->users        = new ArrayCollection();
        $this->posts        = new ArrayCollection();
        $this->editos       = new ArrayCollection();
        $this->noteInternes = new ArrayCollection();
    }

    public function addEdito(Edito $edito): self
    {
        if (!$this->editos->contains($edito)) {
            $this->editos[] = $edito;
            $edito->setFond($this);
        }

        return $this;
    }

    public function addNoteInterne(NoteInterne $noteInterne): self
    {
        if (!$this->noteInternes->contains($noteInterne)) {
            $this->noteInternes[] = $noteInterne;
            $noteInterne->setFond($this);
        }

        return $this;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setImg($this);
        }

        return $this;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setAvatar($this);
        }

        return $this;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    /**
     * @return Collection|Edito[]
     */
    public function getEditos(): Collection
    {
        return $this->editos;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Collection|NoteInterne[]
     */
    public function getNoteInternes(): Collection
    {
        return $this->noteInternes;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getStateChanged()
    {
        return $this->stateChanged;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function removeEdito(Edito $edito): self
    {
        if ($this->editos->removeElement($edito)) {
            // set the owning side to null (unless already changed)
            if ($edito->getFond() === $this) {
                $edito->setFond(null);
            }
        }

        return $this;
    }

    public function removeNoteInterne(NoteInterne $noteInterne): self
    {
        if ($this->noteInternes->removeElement($noteInterne)) {
            // set the owning side to null (unless already changed)
            if ($noteInterne->getFond() === $this) {
                $noteInterne->setFond(null);
            }
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getImg() === $this) {
                $post->setImg(null);
            }
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAvatar() === $this) {
                $user->setAvatar(null);
            }
        }

        return $this;
    }

    public function setDimensions(?array $dimensions): self
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;
    }
}
