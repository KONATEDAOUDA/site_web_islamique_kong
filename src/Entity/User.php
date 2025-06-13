<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide")]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Favorite::class, orphanRemoval: true)]
    private Collection $favorites;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Article::class)]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Podcast::class)]
    private Collection $podcasts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Archive::class)]
    private Collection $archives;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: ForumTopic::class)]
    private Collection $forumTopics;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: ForumPost::class)]
    private Collection $forumPosts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: MaitreIslamique::class)]
    private Collection $maitresIslamiques;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastActivityAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $lastLoginIp = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastLoginUserAgent = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $loginCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $passwordChangedAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $mustChangePassword = false;

    #[ORM\ManyToMany(targetEntity: Role::class)]
    private Collection $customRoles;

    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->podcasts = new ArrayCollection();
        $this->archives = new ArrayCollection();
        $this->forumTopics = new ArrayCollection();
        $this->forumPosts = new ArrayCollection();
        $this->maitresIslamiques = new ArrayCollection();
        $this->customRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        foreach ($this->getCustomRoles() as $role) {
            $roles[] = $role->getRoleName();
        }
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear any temporary sensitive data
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->setUser($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            if ($favorite->getUser() === $this) {
                $favorite->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Podcast>
     */
    public function getPodcasts(): Collection
    {
        return $this->podcasts;
    }

    public function addPodcast(Podcast $podcast): static
    {
        if (!$this->podcasts->contains($podcast)) {
            $this->podcasts->add($podcast);
            $podcast->setAuthor($this);
        }

        return $this;
    }

    public function removePodcast(Podcast $podcast): static
    {
        if ($this->podcasts->removeElement($podcast)) {
            if ($podcast->getAuthor() === $this) {
                $podcast->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Archive>
     */
    public function getArchives(): Collection
    {
        return $this->archives;
    }

    public function addArchive(Archive $archive): static
    {
        if (!$this->archives->contains($archive)) {
            $this->archives->add($archive);
            $archive->setAuthor($this);
        }

        return $this;
    }

    public function removeArchive(Archive $archive): static
    {
        if ($this->archives->removeElement($archive)) {
            if ($archive->getAuthor() === $this) {
                $archive->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumTopic>
     */
    public function getForumTopics(): Collection
    {
        return $this->forumTopics;
    }

    public function addForumTopic(ForumTopic $forumTopic): static
    {
        if (!$this->forumTopics->contains($forumTopic)) {
            $this->forumTopics->add($forumTopic);
            $forumTopic->setAuthor($this);
        }

        return $this;
    }

    public function removeForumTopic(ForumTopic $forumTopic): static
    {
        if ($this->forumTopics->removeElement($forumTopic)) {
            if ($forumTopic->getAuthor() === $this) {
                $forumTopic->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ForumPost>
     */
    public function getForumPosts(): Collection
    {
        return $this->forumPosts;
    }

    public function addForumPost(ForumPost $forumPost): static
    {
        if (!$this->forumPosts->contains($forumPost)) {
            $this->forumPosts->add($forumPost);
            $forumPost->setAuthor($this);
        }

        return $this;
    }

    public function removeForumPost(ForumPost $forumPost): static
    {
        if ($this->forumPosts->removeElement($forumPost)) {
            if ($forumPost->getAuthor() === $this) {
                $forumPost->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MaitreIslamique>
     */
    public function getMaitresIslamiques(): Collection
    {
        return $this->maitresIslamiques;
    }

    public function addMaitreIslamique(MaitreIslamique $maitreIslamique): static
    {
        if (!$this->maitresIslamiques->contains($maitreIslamique)) {
            $this->maitresIslamiques->add($maitreIslamique);
            $maitreIslamique->setAuthor($this);
        }

        return $this;
    }

    public function removeMaitreIslamique(MaitreIslamique $maitreIslamique): static
    {
        if ($this->maitresIslamiques->removeElement($maitreIslamique)) {
            if ($maitreIslamique->getAuthor() === $this) {
                $maitreIslamique->setAuthor(null);
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }
    
    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function getLastActivityAt(): ?\DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeImmutable $lastActivityAt): self
    {
        $this->lastActivityAt = $lastActivityAt;
        return $this;
    }

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): self
    {
        $this->lastLoginIp = $lastLoginIp;
        return $this;
    }

    public function getLastLoginUserAgent(): ?string
    {
        return $this->lastLoginUserAgent;
    }

    public function setLastLoginUserAgent(?string $lastLoginUserAgent): self
    {
        $this->lastLoginUserAgent = $lastLoginUserAgent;
        return $this;
    }

    public function getLoginCount(): int
    {
        return $this->loginCount;
    }

    public function setLoginCount(int $loginCount): self
    {
        $this->loginCount = $loginCount;
        return $this;
    }

    public function incrementLoginCount(): self
    {
        $this->loginCount++;
        return $this;
    }

    public function getPasswordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?\DateTimeImmutable $passwordChangedAt): self
    {
        $this->passwordChangedAt = $passwordChangedAt;
        return $this;
    }

    public function getMustChangePassword(): bool
    {
        return $this->mustChangePassword;
    }

    public function setMustChangePassword(bool $mustChangePassword): self
    {
        $this->mustChangePassword = $mustChangePassword;
        return $this;
    }

    public function getCustomRoles(): Collection { return $this->customRoles; }

    public function addCustomRole(Role $role): self
    {
        if (!$this->customRoles->contains($role)) {
            $this->customRoles[] = $role;
        }
        return $this;
    }
    public function removeCustomRole(Role $role): self
    {
        $this->customRoles->removeElement($role);
        return $this;
    }
}
