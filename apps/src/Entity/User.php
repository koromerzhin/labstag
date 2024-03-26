<?php

namespace Labstag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Entity\Traits\StateableEntity;
use Labstag\Interfaces\EntityTrashInterface;
use Labstag\Repository\UserRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Uploadable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Stringable, EntityTrashInterface
{
    use SoftDeleteableEntity;
    use StateableEntity;

    /**
     * @var int
     */
    protected const DATAUNSERIALIZE = 4;

    #[ORM\Column(type: 'string', nullable: true)]
    protected string $password;

    #[Assert\NotCompromisedPassword]
    protected ?string $plainPassword = null;

    #[ORM\ManyToOne(targetEntity: Groupe::class, inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'refgroupe_id', nullable: true)]
    protected ?Groupe $refgroupe = null;

    #[ORM\Column(type: 'json')]
    protected array $roles = ['ROLE_USER'];

    #[ORM\OneToMany(
        targetEntity: AddressUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $addressUsers;

    #[ORM\ManyToOne(targetEntity: Attachment::class, inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'avatar_id')]
    private ?Attachment $attachment = null;

    #[ORM\OneToMany(
        targetEntity: Bookmark::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $bookmarks;

    #[ORM\OneToMany(
        targetEntity: Edito::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $editos;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $email = null;

    #[ORM\OneToMany(
        targetEntity: EmailUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $emailUsers;

    #[UploadableField(filename: 'avatar', path: 'user/avatar', slug: 'username')]
    private mixed $file;

    #[ORM\OneToMany(
        targetEntity: History::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $histories;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'guid', unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?string $id = null;

    #[ORM\OneToMany(
        targetEntity: LinkUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $linkUsers;

    #[ORM\OneToMany(
        targetEntity: Memo::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $noteInternes;

    #[ORM\OneToMany(
        targetEntity: OauthConnectUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $oauthConnectUsers;

    #[ORM\OneToMany(
        targetEntity: PhoneUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $phoneUsers;

    #[ORM\OneToMany(
        targetEntity: Post::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $posts;

    #[ORM\OneToMany(
        targetEntity: RouteUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $routes;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: false)]
    #[Assert\NotNull]
    private string $username;

    #[ORM\OneToMany(
        targetEntity: WorkflowUser::class,
        mappedBy: 'user',
        cascade: ['persist'],
        orphanRemoval: true
    )
    ]
    private Collection $workflowUsers;

    public function __construct()
    {
        $this->editos            = new ArrayCollection();
        $this->noteInternes      = new ArrayCollection();
        $this->linkUsers         = new ArrayCollection();
        $this->emailUsers        = new ArrayCollection();
        $this->phoneUsers        = new ArrayCollection();
        $this->addressUsers      = new ArrayCollection();
        $this->oauthConnectUsers = new ArrayCollection();
        $this->routes            = new ArrayCollection();
        $this->workflowUsers     = new ArrayCollection();
        $this->posts             = new ArrayCollection();
        $this->bookmarks         = new ArrayCollection();
        $this->histories         = new ArrayCollection();
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->username,
            $this->email,
            $this->password,
        ];
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }

    public function __unserialize(array $data): void
    {
        if (self::DATAUNSERIALIZE === count($data)) {
            [
                $this->id,
                $this->username,
                $this->email,
                $this->password,
            ] = $data;
        }
    }

    public function addAddressUser(AddressUser $addressUser): self
    {
        if (!$this->addressUsers->contains($addressUser)) {
            $this->addressUsers[] = $addressUser;
            $addressUser->setRefuser($this);
        }

        return $this;
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks[] = $bookmark;
            $bookmark->setRefuser($this);
        }

        return $this;
    }

    public function addEdito(Edito $edito): self
    {
        if (!$this->editos->contains($edito)) {
            $this->editos[] = $edito;
            $edito->setRefuser($this);
        }

        return $this;
    }

    public function addEmailUser(EmailUser $emailUser): self
    {
        if (!$this->emailUsers->contains($emailUser)) {
            $emailUser->setRefuser($this);
            $this->emailUsers[] = $emailUser;
        }

        return $this;
    }

    public function addHistory(History $history): self
    {
        if (!$this->histories->contains($history)) {
            $this->histories[] = $history;
            $history->setRefuser($this);
        }

        return $this;
    }

    public function addLinkUser(LinkUser $linkUser): self
    {
        if (!$this->linkUsers->contains($linkUser)) {
            $linkUser->setRefuser($this);
            $this->linkUsers[] = $linkUser;
        }

        return $this;
    }

    public function addMemo(Memo $memo): self
    {
        if (!$this->noteInternes->contains($memo)) {
            $this->noteInternes[] = $memo;
            $memo->setRefuser($this);
        }

        return $this;
    }

    public function addNoteInterne(Memo $memo): self
    {
        if (!$this->noteInternes->contains($memo)) {
            $this->noteInternes[] = $memo;
            $memo->setRefuser($this);
        }

        return $this;
    }

    public function addOauthConnectUser(OauthConnectUser $oauthConnectUser): self
    {
        if (!$this->oauthConnectUsers->contains($oauthConnectUser)) {
            $this->oauthConnectUsers[] = $oauthConnectUser;
            $oauthConnectUser->setRefuser($this);
        }

        return $this;
    }

    public function addOauthConnectUsers(
        OauthConnectUser $oauthConnectUser
    ): self
    {
        if (!$this->oauthConnectUsers->contains($oauthConnectUser)) {
            $this->oauthConnectUsers[] = $oauthConnectUser;
            $oauthConnectUser->setRefuser($this);
        }

        return $this;
    }

    public function addPhoneUser(PhoneUser $phoneUser): self
    {
        if (!$this->phoneUsers->contains($phoneUser)) {
            $this->phoneUsers[] = $phoneUser;
            $phoneUser->setRefuser($this);
        }

        return $this;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setRefuser($this);
        }

        return $this;
    }

    public function addRoute(RouteUser $routeUser): self
    {
        if (!$this->routes->contains($routeUser)) {
            $this->routes[] = $routeUser;
            $routeUser->setRefuser($this);
        }

        return $this;
    }

    public function addWorkflowUser(WorkflowUser $workflowUser): self
    {
        if (!$this->workflowUsers->contains($workflowUser)) {
            $this->workflowUsers[] = $workflowUser;
            $workflowUser->setRefuser($this);
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getAddressUsers(): Collection
    {
        return $this->addressUsers;
    }

    public function getAvatar(): ?Attachment
    {
        return $this->attachment;
    }

    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function getEditos(): Collection
    {
        return $this->editos;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function getEmailUsers(): Collection
    {
        return $this->emailUsers;
    }

    public function getFile(): mixed
    {
        return $this->file;
    }

    public function getHistories(): Collection
    {
        return $this->histories;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLinkUsers(): Collection
    {
        return $this->linkUsers;
    }

    public function getMemos(): Collection
    {
        return $this->noteInternes;
    }

    public function getNoteInternes(): Collection
    {
        return $this->noteInternes;
    }

    public function getOauthConnectUsers(): Collection
    {
        return $this->oauthConnectUsers;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function getPhoneUsers(): Collection
    {
        return $this->phoneUsers;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getRefgroupe(): ?Groupe
    {
        return $this->refgroupe;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function getWorkflowUsers(): Collection
    {
        return $this->workflowUsers;
    }

    public function removeAddressUser(AddressUser $addressUser): self
    {
        if ($this->addressUsers->contains($addressUser)) {
            $this->addressUsers->removeElement($addressUser);
            // set the owning side to null (unless already changed)
            if ($addressUser->getRefuser() === $this) {
                $addressUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        $this->removeElementUser(
            element: $this->bookmarks,
            bookmark: $bookmark
        );

        return $this;
    }

    public function removeEdito(Edito $edito): self
    {
        if ($this->editos->contains($edito)) {
            $this->editos->removeElement($edito);
            // set the owning side to null (unless already changed)
            if ($edito->getRefuser() === $this) {
                $edito->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeEmailUser(EmailUser $emailUser): self
    {
        if ($this->emailUsers->contains($emailUser)) {
            $this->emailUsers->removeElement($emailUser);
            // set the owning side to null (unless already changed)
            if ($emailUser->getRefuser() === $this) {
                $emailUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeHistory(History $history): self
    {
        $this->removeElementUser(
            element: $this->histories,
            history: $history
        );

        return $this;
    }

    public function removeLinkUser(LinkUser $linkUser): self
    {
        if ($this->linkUsers->contains($linkUser)) {
            $this->linkUsers->removeElement($linkUser);
            // set the owning side to null (unless already changed)
            if ($linkUser->getRefuser() === $this) {
                $linkUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeMemo(Memo $memo): self
    {
        if ($this->noteInternes->contains($memo)) {
            $this->noteInternes->removeElement($memo);
            // set the owning side to null (unless already changed)
            if ($memo->getRefuser() === $this) {
                $memo->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeNoteInterne(Memo $memo): self
    {
        $this->removeElementUser(
            element: $this->noteInternes,
            memo: $memo
        );

        return $this;
    }

    public function removeOauthConnectUser(OauthConnectUser $oauthConnectUser): self
    {
        $this->removeElementUser(
            element: $this->oauthConnectUsers,
            oauthConnectUser: $oauthConnectUser
        );

        return $this;
    }

    public function removeOauthConnectUsers(
        OauthConnectUser $oauthConnectUser
    ): self
    {
        if ($this->oauthConnectUsers->contains($oauthConnectUser)) {
            $this->oauthConnectUsers->removeElement($oauthConnectUser);
            // set the owning side to null (unless already changed)
            if ($oauthConnectUser->getRefuser() === $this) {
                $oauthConnectUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function removePhoneUser(PhoneUser $phoneUser): self
    {
        if ($this->phoneUsers->contains($phoneUser)) {
            $this->phoneUsers->removeElement($phoneUser);
            // set the owning side to null (unless already changed)
            if ($phoneUser->getRefuser() === $this) {
                $phoneUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        $this->removeElementUser(
            element: $this->posts,
            post: $post
        );

        return $this;
    }

    public function removeRoute(RouteUser $routeUser): self
    {
        $this->removeElementUser(
            element: $this->routes,
            routeUser: $routeUser
        );

        return $this;
    }

    public function removeWorkflowUser(WorkflowUser $workflowUser): self
    {
        $this->removeElementUser(
            element: $this->workflowUsers,
            workflowUser: $workflowUser
        );

        return $this;
    }

    public function setAvatar(?Attachment $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setFile(mixed $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->setPassword('');
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function setRefgroupe(?Groupe $groupe): self
    {
        $this->refgroupe = $groupe;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    private function removeElementUser(
        Collection $element,
        ?Bookmark $bookmark = null,
        ?History $history = null,
        ?Memo $memo = null,
        ?OauthConnectUser $oauthConnectUser = null,
        ?Post $post = null,
        ?RouteUser $routeUser = null,
        ?WorkflowUser $workflowUser = null
    ): void
    {
        $variable = $bookmark ?? $history ?? $memo ?? $oauthConnectUser ?? $post ?? $routeUser ?? $workflowUser;
        if (!is_null($variable) && $element->removeElement($variable) && $variable->getRefuser() === $this) {
            $variable->setRefuser(null);
        }
    }
}
