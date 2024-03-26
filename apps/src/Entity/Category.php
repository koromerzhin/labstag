<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\CategoryRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements Stringable, EntityTrashInterface
{
    use SoftDeleteableEntity;

    #[ORM\OneToMany(targetEntity: Bookmark::class, mappedBy: 'category', cascade: ['persist'], orphanRemoval: true)]
    private Collection $bookmarks;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?Category $category = null;

    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'category', cascade: ['persist'], orphanRemoval: true)]
    private Collection $children;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'category', cascade: ['persist'], orphanRemoval: true)]
    private Collection $posts;

    #[Gedmo\Slug(updatable: false, fields: ['name'])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->children  = new ArrayCollection();
        $this->posts     = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
    }

    public function __toString(): string
    {
        $category = $this->getParent();
        $text     = is_null($category) ? '' : $category.' - ';

        return $text.$this->getName();
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks[] = $bookmark;
            $bookmark->setRefcategory($this);
        }

        return $this;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setRefcategory($this);
        }

        return $this;
    }

    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getParent(): ?self
    {
        return $this->category;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        $this->removeElementCategory(
            element: $this->bookmarks,
            bookmark: $bookmark
        );

        return $this;
    }

    public function removeChild(self $child): self
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($child) && $child->getParent() === $this) {
            $child->setParent(null);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        $this->removeElementCategory(
            element: $this->posts,
            post: $post
        );

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setParent(?self $parent): self
    {
        $this->category = $parent;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    private function removeElementCategory(
        Collection $element,
        ?Bookmark $bookmark = null,
        ?Post $post = null
    ): void
    {
        if (is_null($bookmark) && is_null($post)) {
            return;
        }

        $variable = is_null($bookmark) ? $post : $bookmark;

        if ($element->removeElement($variable) && $variable->getRefcategory() === $this) {
            $variable->setRefcategory(null);
        }
    }
}
