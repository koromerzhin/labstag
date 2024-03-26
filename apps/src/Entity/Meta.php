<?php

namespace Labstag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Labstag\Interfaces\EntityInterface;
use Labstag\Repository\MetaRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: MetaRepository::class)]
class Meta implements Stringable, EntityInterface
{

    #[ORM\ManyToOne(targetEntity: Chapter::class, inversedBy: 'metas', cascade: ['persist'])]
    private ?Chapter $chapter = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Edito::class, inversedBy: 'metas', cascade: ['persist'])]
    private ?Edito $edito = null;

    #[ORM\ManyToOne(targetEntity: History::class, inversedBy: 'metas', cascade: ['persist'])]
    private ?History $history = null;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $keywords = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'metas', cascade: ['persist'])]
    private ?Page $page = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'metas', cascade: ['persist'])]
    private ?Post $post = null;

    #[ORM\ManyToOne(targetEntity: Render::class, inversedBy: 'metas', cascade: ['persist'])]
    private ?Render $render = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    public function __toString(): string
    {
        return implode(
            ' ',
            [
                $this->getTitle(),
                $this->getDescription(),
                $this->getKeywords(),
            ]
        );
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEdito(): ?Edito
    {
        return $this->edito;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function getRender(): ?Render
    {
        return $this->render;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setChapter(?Chapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setEdito(?Edito $edito): self
    {
        $this->edito = $edito;

        return $this;
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function setRender(?Render $render): self
    {
        $this->render = $render;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
