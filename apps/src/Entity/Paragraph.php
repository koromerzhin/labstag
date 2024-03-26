<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Labstag\Entity\Paragraph\Form;
use Labstag\Entity\Paragraph\Image;
use Labstag\Entity\Paragraph\Text;
use Labstag\Entity\Paragraph\TextImage;
use Labstag\Entity\Paragraph\Video;
use Labstag\Entity\Traits\Paragraph\BookmarkEntity;
use Labstag\Entity\Traits\Paragraph\EditoEntity;
use Labstag\Entity\Traits\Paragraph\HistoryEntity;
use Labstag\Entity\Traits\Paragraph\PostEntity;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\ParagraphRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: ParagraphRepository::class)]
class Paragraph implements EntityInterface
{
    use BookmarkEntity;
    use EditoEntity;
    use HistoryEntity;
    use PostEntity;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $background = null;

    #[ORM\ManyToOne(targetEntity: Chapter::class, inversedBy: 'paragraphs', cascade: ['persist'])]
    private ?Chapter $chapter = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\OneToMany(mappedBy: 'paragraph', targetEntity: Form::class)]
    private Collection $forms;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'paragraph', cascade: ['persist'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\ManyToOne(targetEntity: Layout::class, inversedBy: 'paragraphs', cascade: ['persist'])]
    private ?Layout $layout = null;

    #[ORM\ManyToOne(targetEntity: Memo::class, inversedBy: 'paragraphs', cascade: ['persist'])]
    private ?Memo $memo = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'paragraphs', cascade: ['persist'])]
    private ?Page $page = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\OneToMany(targetEntity: TextImage::class, mappedBy: 'paragraph', cascade: ['persist'], orphanRemoval: true)]
    private Collection $textImages;

    #[ORM\OneToMany(targetEntity: Text::class, mappedBy: 'paragraph', cascade: ['persist'], orphanRemoval: true)]
    private Collection $texts;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $type = null;

    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'paragraph', cascade: ['persist'], orphanRemoval: true)]
    private Collection $videos;

    public function __construct()
    {
        $this->texts              = new ArrayCollection();
        $this->bookmarks          = new ArrayCollection();
        $this->histories          = new ArrayCollection();
        $this->editos             = new ArrayCollection();
        $this->posts              = new ArrayCollection();
        $this->postLists          = new ArrayCollection();
        $this->historyLists       = new ArrayCollection();
        $this->bookmarkLists      = new ArrayCollection();
        $this->postArchives       = new ArrayCollection();
        $this->postUsers          = new ArrayCollection();
        $this->postLibelles       = new ArrayCollection();
        $this->bookmarkLibelles   = new ArrayCollection();
        $this->bookmarkCategories = new ArrayCollection();
        $this->postYears          = new ArrayCollection();
        $this->postShows          = new ArrayCollection();
        $this->postCategories     = new ArrayCollection();
        $this->postHeaders        = new ArrayCollection();
        $this->historyUsers       = new ArrayCollection();
        $this->historyChapters    = new ArrayCollection();
        $this->historyShows       = new ArrayCollection();
        $this->editoShows         = new ArrayCollection();
        $this->editoHeaders       = new ArrayCollection();
        $this->videos             = new ArrayCollection();
        $this->images             = new ArrayCollection();
        $this->textImages         = new ArrayCollection();
        $this->forms              = new ArrayCollection();
    }

    public function addForm(Form $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms->add($form);
            $form->setParagraph($this);
        }

        return $this;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setParagraph($this);
        }

        return $this;
    }

    public function addText(Text $text): self
    {
        if (!$this->texts->contains($text)) {
            $this->texts[] = $text;
            $text->setParagraph($this);
        }

        return $this;
    }

    public function addTextImage(TextImage $textImage): self
    {
        if (!$this->textImages->contains($textImage)) {
            $this->textImages[] = $textImage;
            $textImage->setParagraph($this);
        }

        return $this;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setParagraph($this);
        }

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return Collection<int, Form>
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    public function getMemo(): ?Memo
    {
        return $this->memo;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @return Collection<int, TextImage>
     */
    public function getTextImages(): Collection
    {
        return $this->textImages;
    }

    /**
     * @return Collection<int, Text>
     */
    public function getTexts(): Collection
    {
        return $this->texts;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function removeForm(Form $form): self
    {
        // set the owning side to null (unless already changed)
        if ($this->forms->removeElement($form) && $form->getParagraph() === $this) {
            $form->setParagraph(null);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        // set the owning side to null (unless already changed)
        if ($this->images->removeElement($image) && $image->getParagraph() === $this) {
            $image->setParagraph(null);
        }

        return $this;
    }

    public function removeText(Text $text): self
    {
        // set the owning side to null (unless already changed)
        if ($this->texts->removeElement($text) && $text->getParagraph() === $this) {
            $text->setParagraph(null);
        }

        return $this;
    }

    public function removeTextImage(TextImage $textImage): self
    {
        // set the owning side to null (unless already changed)
        if ($this->textImages->removeElement($textImage) && $textImage->getParagraph() === $this) {
            $textImage->setParagraph(null);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        // set the owning side to null (unless already changed)
        if ($this->videos->removeElement($video) && $video->getParagraph() === $this) {
            $video->setParagraph(null);
        }

        return $this;
    }

    public function setBackground(?string $background): self
    {
        $this->background = $background;

        return $this;
    }

    public function setChapter(?Chapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function setLayout(?Layout $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function setMemo(?Memo $memo): self
    {
        $this->memo = $memo;

        return $this;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
