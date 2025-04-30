<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Article::class)]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'favoredBy')]
    private Collection $favoriteArticles;

    #[ORM\ManyToMany(targetEntity: Podcast::class, mappedBy: 'favoredBy')]
    private Collection $favoritePodcasts;

    #[ORM\ManyToMany(targetEntity: Archive::class, mappedBy: 'favoredBy')]
    private Collection $favoriteArchives;

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

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favoriteArticles = new ArrayCollection();
        $this->favoritePodcasts = new ArrayCollection();
        $this->favoriteArchives = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->podcasts = new ArrayCollection();
        $this->archives = new ArrayCollection();
        $this->forumTopics = new ArrayCollection();
        $this->forumPosts = new ArrayCollection();
        $this->maitresIslamiques = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
            // set the owning side to null (unless already changed)
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
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getFavoriteArticles(): Collection
    {
        return $this->favoriteArticles;
    }

    public function addFavoriteArticle(Article $article): static
    {
        if (!$this->favoriteArticles->contains($article)) {
            $this->favoriteArticles->add($article);
            $article->addFavoredBy($this);
        }

        return $this;
    }

    public function removeFavoriteArticle(Article $article): static
    {
        if ($this->favoriteArticles->removeElement($article)) {
            $article->removeFavoredBy($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Podcast>
     */
    public function getFavoritePodcasts(): Collection
    {
        return $this->favoritePodcasts;
    }

    public function addFavoritePodcast(Podcast $podcast): static
    {
        if (!$this->favoritePodcasts->contains($podcast)) {
            $this->favoritePodcasts->add($podcast);
            $podcast->addFavoredBy($this);
        }

        return $this;
    }

    public function removeFavoritePodcast(Podcast $podcast): static
    {
        if ($this->favoritePodcasts->removeElement($podcast)) {
            $podcast->removeFavoredBy($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Archive>
     */
    public function getFavoriteArchives(): Collection
    {
        return $this->favoriteArchives;
    }

    public function addFavoriteArchive(Archive $archive): static
    {
        if (!$this->favoriteArchives->contains($archive)) {
            $this->favoriteArchives->add($archive);
            $archive->addFavoredBy($this);
        }

        return $this;
    }

    public function removeFavoriteArchive(Archive $archive): static
    {
        if ($this->favoriteArchives->removeElement($archive)) {
            $archive->removeFavoredBy($this);
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
}
