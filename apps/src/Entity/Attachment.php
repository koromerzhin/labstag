<?php

namespace Labstag\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Entity\Paragraph\Image;
use Labstag\Entity\Paragraph\TextImage;
use Labstag\Entity\Paragraph\Video;
use Labstag\Entity\Traits\StateableEntity;
use Labstag\Repository\AttachmentRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: AttachmentRepository::class)]
#[ApiResource]
class Attachment
{
    use SoftDeleteableEntity;
    use StateableEntity;

    #[ORM\OneToMany(targetEntity: Bookmark::class, mappedBy: 'attachment', cascade: ['persist'], orphanRemoval: true)]
    private $bookmarks;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $code = null;

    #[ORM\OneToMany(targetEntity: Edito::class, mappedBy: 'fond', cascade: ['persist'], orphanRemoval: true)]
    private $editos;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mimeType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\OneToMany(targetEntity: Memo::class, mappedBy: 'fond', cascade: ['persist'], orphanRemoval: true)]
    private $noteInternes;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'attachment', cascade: ['persist'], orphanRemoval: true)]
    private $paragraphImages;

    #[ORM\OneToMany(targetEntity: TextImage::class, mappedBy: 'attachment', cascade: ['persist'], orphanRemoval: true)]
    private $paragraphTextImages;

    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'attachment', cascade: ['persist'], orphanRemoval: true)]
    private $paragraphVideos;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'attachment', cascade: ['persist'], orphanRemoval: true)]
    private $posts;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $size;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'avatar', cascade: ['persist'], orphanRemoval: true)]
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->editos = new ArrayCollection();
        $this->noteInternes = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
        $this->paragraphVideos = new ArrayCollection();
        $this->paragraphImages = new ArrayCollection();
        $this->paragraphTextImages = new ArrayCollection();
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks[] = $bookmark;
            $bookmark->setImg($this);
        }

        return $this;
    }

    public function addEdito(Edito $edito): self
    {
        if (!$this->editos->contains($edito)) {
            $this->editos[] = $edito;
            $edito->setFond($this);
        }

        return $this;
    }

    public function addMemo(Memo $memo): self
    {
        if (!$this->noteInternes->contains($memo)) {
            $this->noteInternes[] = $memo;
            $memo->setFond($this);
        }

        return $this;
    }

    public function addNoteInterne(Memo $memo): self
    {
        if (!$this->noteInternes->contains($memo)) {
            $this->noteInternes[] = $memo;
            $memo->setFond($this);
        }

        return $this;
    }

    public function addParagraphImage(Image $image): self
    {
        if (!$this->paragraphImages->contains($image)) {
            $this->paragraphImages[] = $image;
            $image->setImage($this);
        }

        return $this;
    }

    public function addParagraphTextImage(TextImage $textimage): self
    {
        if (!$this->paragraphTextImages->contains($textimage)) {
            $this->paragraphTextImages[] = $textimage;
            $textimage->setImage($this);
        }

        return $this;
    }

    public function addParagraphVideo(Video $video): self
    {
        if (!$this->paragraphVideos->contains($video)) {
            $this->paragraphVideos[] = $video;
            $video->setImage($this);
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

    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getEditos(): Collection
    {
        return $this->editos;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMemos(): Collection
    {
        return $this->noteInternes;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNoteInternes(): Collection
    {
        return $this->noteInternes;
    }

    public function getParagraphImages(): Collection
    {
        return $this->paragraphImages;
    }

    public function getParagraphTextImages(): Collection
    {
        return $this->paragraphTextImages;
    }

    public function getParagraphVideos(): Collection
    {
        return $this->paragraphVideos;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        // set the owning side to null (unless already changed)
        if ($this->bookmarks->removeElement($bookmark) && $bookmark->getImg() === $this) {
            $bookmark->setImg(null);
        }

        return $this;
    }

    public function removeEdito(Edito $edito): self
    {
        // set the owning side to null (unless already changed)
        if ($this->editos->removeElement($edito) && $edito->getFond() === $this) {
            $edito->setFond(null);
        }

        return $this;
    }

    public function removeMemo(Memo $memo): self
    {
        // set the owning side to null (unless already changed)
        if ($this->noteInternes->removeElement($memo) && $memo->getFond() === $this) {
            $memo->setFond(null);
        }

        return $this;
    }

    public function removeNoteInterne(Memo $memo): self
    {
        // set the owning side to null (unless already changed)
        if ($this->noteInternes->removeElement($memo) && $memo->getFond() === $this) {
            $memo->setFond(null);
        }

        return $this;
    }

    public function removeParagraphImage(Image $image): self
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphImages->removeElement($image) && $image->getImage() === $this) {
            $image->setImage(null);
        }

        return $this;
    }

    public function removeParagraphTextImage(TextImage $textimage): self
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphTextImages->removeElement($textimage) && $textimage->getImage() === $this) {
            $textimage->setImage(null);
        }

        return $this;
    }

    public function removeParagraphVideo(Video $paragraphVideo): self
    {
        // set the owning side to null (unless already changed)
        if ($this->paragraphVideos->removeElement($paragraphVideo) && $paragraphVideo->getImage() === $this) {
            $paragraphVideo->setImage(null);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        // set the owning side to null (unless already changed)
        if ($this->posts->removeElement($post) && $post->getImg() === $this) {
            $post->setImg(null);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        // set the owning side to null (unless already changed)
        if ($this->users->removeElement($user) && $user->getAvatar() === $this) {
            $user->setAvatar(null);
        }

        return $this;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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
}
