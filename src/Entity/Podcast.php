<?php

namespace App\Entity;

use App\Repository\PodcastRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\StateEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: PodcastRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Podcast
{
    use StateEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Assert\NotBlank(message: 'Le type est obligatoire')]
    #[Assert\Choice(choices: ['audio', 'video'], message: 'Le type doit être audio ou video')]
    #[ORM\Column(length: 255)]
    private ?string $type = null; // 'audio' or 'video'

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\ManyToOne(inversedBy: 'podcasts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favoritePodcasts')]
    private Collection $favoredBy;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $viewCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $downloadCount = 0;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $calculatedDuration = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $externalUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isFeatured = false;

    #[ORM\Column(type: 'json')]
    private array $tags = [];

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

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
        if (!in_array($type, ['audio', 'video'])) {
            throw new \InvalidArgumentException("Le type doit être 'audio' ou 'video'");
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

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): self
    {
        $this->viewCount = $viewCount;
        return $this;
    }

    public function incrementViewCount(): self
    {
        $this->viewCount++;
        return $this;
    }

    public function getDownloadCount(): int
    {
        return $this->downloadCount;
    }

    public function setDownloadCount(int $downloadCount): self
    {
        $this->downloadCount = $downloadCount;
        return $this;
    }

    public function incrementDownloadCount(): self
    {
        $this->downloadCount++;
        return $this;
    }

    public function getCalculatedDuration(): ?string
    {
        return $this->calculatedDuration;
    }

    public function setCalculatedDuration(?string $calculatedDuration): self
    {
        $this->calculatedDuration = $calculatedDuration;
        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;
        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }
}