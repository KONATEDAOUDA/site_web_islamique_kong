<?php

namespace App\Entity;

use App\Repository\EnseignementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\StateEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: EnseignementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Enseignement
{
    use StateEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null; // 'texte', 'audio', 'video'

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\ManyToOne(inversedBy: 'enseignementsList')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MaitreIslamique $maitre = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $viewCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $completionCount = 0;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $supportFileSize = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $audioFileSize = null;

    public function __construct()
    {
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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
            $this->slug = (string) $slugger->slug((string) $this->titre)->lower();
        }
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        // Valider que le type est parmi les valeurs autorisées
        if (!in_array($type, ['texte', 'audio', 'video'])) {
            throw new \InvalidArgumentException("Le type doit être 'texte', 'audio' ou 'video'");
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

    public function getMaitre(): ?MaitreIslamique
    {
        return $this->maitre;
    }

    public function setMaitre(?MaitreIslamique $maitre): static
    {
        $this->maitre = $maitre;

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

    public function getCompletionCount(): int
    {
        return $this->completionCount;
    }

    public function setCompletionCount(int $completionCount): self
    {
        $this->completionCount = $completionCount;
        return $this;
    }

    public function incrementCompletionCount(): self
    {
        $this->completionCount++;
        return $this;
    }

    public function getSupportFileSize(): ?int
    {
        return $this->supportFileSize;
    }

    public function setSupportFileSize(?int $supportFileSize): self
    {
        $this->supportFileSize = $supportFileSize;
        return $this;
    }

    public function getAudioFileSize(): ?int
    {
        return $this->audioFileSize;
    }

    public function setAudioFileSize(?int $audioFileSize): self
    {
        $this->audioFileSize = $audioFileSize;
        return $this;
    }

}
