<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Traits\StateEntity;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)] 
class Favorite
{
    use StateEntity;
    use TimestampableEntity;

    public const TYPE_ARTICLE = 'article';
    public const TYPE_PODCAST = 'podcast';
    public const TYPE_ARCHIVE = 'archive';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $contentType = null; // 'article', 'podcast', 'archive'

    #[ORM\Column]
    private ?int $contentId = null;

    public function __construct()
    {
        // Le trait TimestampableEntity gère déjà les dates created/updated
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        if (!in_array($contentType, [self::TYPE_ARTICLE, self::TYPE_PODCAST, self::TYPE_ARCHIVE])) {
            throw new \InvalidArgumentException("Invalid content type");
        }

        $this->contentType = $contentType;

        return $this;
    }

    public function getContentId(): ?int
    {
        return $this->contentId;
    }

    public function setContentId(int $contentId): static
    {
        $this->contentId = $contentId;

        return $this;
    }

    // Méthodes pratiques pour gérer les différents types de contenu
    public function isArticle(): bool
    {
        return $this->contentType === self::TYPE_ARTICLE;
    }

    public function isPodcast(): bool
    {
        return $this->contentType === self::TYPE_PODCAST;
    }

    public function isArchive(): bool
    {
        return $this->contentType === self::TYPE_ARCHIVE;
    }

    // Le trait TimestampableEntity fournit déjà getCreatedAt() et getUpdatedAt()
}