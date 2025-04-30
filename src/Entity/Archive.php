<?php

namespace App\Entity;

use App\Repository\ArchiveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: ArchiveRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Archive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null; // 'article', 'audio', 'video'

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'archives')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favoriteArchives')]
    private Collection $favoredBy;

    #[ORM\Column(nullable: true)]
    private ?int $periodYear = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $historicalContext = null;

    public function __construct()
    {
        $this->favoredBy = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function computeSlug(SluggerInterface $slugger): void
    {
        if (!$this->slug || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this->title)->lower();
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        // Valider que le type est parmi les valeurs autorisées
        if (!in_array($type, ['article', 'audio', 'video'])) {
            throw new \InvalidArgumentException("Le type doit être 'article', 'audio' ou 'video'");
        }
        
        $this->type = $type;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function isIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFavoredBy(): Collection
    {
        return $this->favoredBy;
    }

    public function addFavoredBy(User $user): static
    {
        if (!$this->favoredBy->contains($user)) {
            $this->favoredBy->add($user);
        }

        return $this;
    }

    public function removeFavoredBy(User $user): static
    {
        $this->favoredBy->removeElement($user);

        return $this;
    }

    public function getPeriodYear(): ?int
    {
        return $this->periodYear;
    }

    public function setPeriodYear(?int $periodYear): static
    {
        $this->periodYear = $periodYear;

        return $this;
    }

    public function getHistoricalContext(): ?string
    {
        return $this->historicalContext;
    }

    public function setHistoricalContext(?string $historicalContext): static
    {
        $this->historicalContext = $historicalContext;

        return $this;
    }
}
