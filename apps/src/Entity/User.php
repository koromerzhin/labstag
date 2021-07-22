<?php

namespace Labstag\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Labstag\Annotation\Uploadable;
use Labstag\Annotation\UploadableField;
use Labstag\Repository\UserRepository;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Uploadable()
 */
class User implements UserInterface, Serializable
{
    use SoftDeleteableEntity;

    /**
     * @ORM\OneToMany(
     *  targetEntity=AdresseUser::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $adresseUsers;

    /**
     * @ORM\ManyToOne(targetEntity=Attachment::class, inversedBy="users")
     */
    protected $avatar;

    /**
     * @ORM\OneToMany(
     *  targetEntity=Edito::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $editos;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\OneToMany(
     *  targetEntity=EmailUser::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $emailUsers;

    /**
     * @UploadableField(filename="avatar", path="user/avatar", slug="username")
     */
    protected $file;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", unique=true)
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *  targetEntity=LienUser::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $lienUsers;

    /**
     * @ORM\OneToMany(
     *  targetEntity=NoteInterne::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $noteInternes;

    /**
     * @ORM\OneToMany(
     *  targetEntity=OauthConnectUser::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $oauthConnectUsers;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @ORM\OneToMany(
     *  targetEntity=PhoneUser::class,
     *  mappedBy="refuser",
     *  cascade={"persist"}
     * )
     */
    protected $phoneUsers;

    /**
     * @var string|null
     */
    protected $plainPassword;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    protected Groupe $refgroupe;

    /**
     * @ORM\Column(type="json")
     */
    protected array $roles = [];

    /**
     * @ORM\OneToMany(targetEntity=RouteUser::class, mappedBy="refuser")
     */
    protected $routes;

    /**
     * @ORM\Column(type="array")
     */
    protected $state;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     * @Assert\NotNull
     */
    protected $username;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="refuser")
     */
    private $posts;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="state_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"state"})
     */
    private $stateChanged;

    /**
     * @ORM\OneToMany(targetEntity=WorkflowUser::class, mappedBy="refuser")
     */
    private $workflowUsers;

    public function __construct()
    {
        $this->editos            = new ArrayCollection();
        $this->noteInternes      = new ArrayCollection();
        $this->lienUsers         = new ArrayCollection();
        $this->emailUsers        = new ArrayCollection();
        $this->phoneUsers        = new ArrayCollection();
        $this->adresseUsers      = new ArrayCollection();
        $this->oauthConnectUsers = new ArrayCollection();
        $this->roles             = ['ROLE_USER'];
        $this->routes            = new ArrayCollection();
        $this->workflowUsers     = new ArrayCollection();
        $this->posts             = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    public function addAdresseUser(AdresseUser $adresseUser): self
    {
        if (!$this->adresseUsers->contains($adresseUser)) {
            $this->adresseUsers[] = $adresseUser;
            $adresseUser->setRefuser($this);
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

    public function addLienUser(LienUser $lienUser): self
    {
        if (!$this->lienUsers->contains($lienUser)) {
            $lienUser->setRefuser($this);
            $this->lienUsers[] = $lienUser;
        }

        return $this;
    }

    public function addNoteInterne(NoteInterne $noteInterne): self
    {
        if (!$this->noteInternes->contains($noteInterne)) {
            $this->noteInternes[] = $noteInterne;
            $noteInterne->setRefuser($this);
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

    public function addRoute(RouteUser $route): self
    {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
            $route->setRefuser($this);
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

    public function getAdresseUsers()
    {
        return $this->adresseUsers;
    }

    public function getAvatar(): ?Attachment
    {
        return $this->avatar;
    }

    public function getEditos()
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

    public function getEmailUsers()
    {
        return $this->emailUsers;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLienUsers()
    {
        return $this->lienUsers;
    }

    public function getNoteInternes()
    {
        return $this->noteInternes;
    }

    public function getOauthConnectUsers()
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

    public function getPhoneUsers()
    {
        return $this->phoneUsers;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @return Collection|Post[]
     */
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

    /**
     * @return Collection|RouteUser[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): string
    {
        return '';
        // not needed when using the "bcrypt" algorithm in security.yaml
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @return Collection|WorkflowUser[]
     */
    public function getWorkflowUsers(): Collection
    {
        return $this->workflowUsers;
    }

    public function removeAdresseUser(AdresseUser $adresseUser): self
    {
        if ($this->adresseUsers->contains($adresseUser)) {
            $this->adresseUsers->removeElement($adresseUser);
            // set the owning side to null (unless already changed)
            if ($adresseUser->getRefuser() === $this) {
                $adresseUser->setRefuser(null);
            }
        }

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

    public function removeLienUser(LienUser $lienUser): self
    {
        if ($this->lienUsers->contains($lienUser)) {
            $this->lienUsers->removeElement($lienUser);
            // set the owning side to null (unless already changed)
            if ($lienUser->getRefuser() === $this) {
                $lienUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeNoteInterne(NoteInterne $noteInterne): self
    {
        if ($this->noteInternes->contains($noteInterne)) {
            $this->noteInternes->removeElement($noteInterne);
            // set the owning side to null (unless already changed)
            if ($noteInterne->getRefuser() === $this) {
                $noteInterne->setRefuser(null);
            }
        }

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
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getRefuser() === $this) {
                $post->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeRoute(RouteUser $route): self
    {
        if ($this->routes->removeElement($route)) {
            // set the owning side to null (unless already changed)
            if ($route->getRefuser() === $this) {
                $route->setRefuser(null);
            }
        }

        return $this;
    }

    public function removeWorkflowUser(WorkflowUser $workflowUser): self
    {
        if ($this->workflowUsers->removeElement($workflowUser)) {
            // set the owning side to null (unless already changed)
            if ($workflowUser->getRefuser() === $this) {
                $workflowUser->setRefuser(null);
            }
        }

        return $this;
    }

    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->username,
                $this->password,
            ]
        );
    }

    public function setAvatar(?Attachment $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setFile($file): self
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

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password) = unserialize($serialized);
    }
}
