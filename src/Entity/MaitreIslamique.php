<?php

namespace App\Entity;

use App\Repository\MaitreIslamiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\StateEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: MaitreIslamiqueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MaitreIslamique
{
    use StateEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $biographie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $enseignements = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(nullable: true)]
    private ?int $anneeNaissance = null;

    #[ORM\Column(nullable: true)]
    private ?int $anneeDeces = null;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'maitresIslamiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'maitre', targetEntity: Enseignement::class, orphanRemoval: true)]
    private Collection $enseignementsList;

    public function __construct()
    {
        $this->enseignementsList = new ArrayCollection();
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
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
            $this->slug = (string) $slugger->slug((string) $this->getNomComplet())->lower();
        }
    }

    public function getBiographie(): ?string
    {
        return $this->biographie;
    }

    public function setBiographie(string $biographie): static
    {
        $this->biographie = $biographie;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getEnseignements(): ?string
    {
        return $this->enseignements;
    }

    public function setEnseignements(?string $enseignements): static
    {
        $this->enseignements = $enseignements;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getAnneeNaissance(): ?int
    {
        return $this->anneeNaissance;
    }

    public function setAnneeNaissance(?int $anneeNaissance): static
    {
        $this->anneeNaissance = $anneeNaissance;

        return $this;
    }

    public function getAnneeDeces(): ?int
    {
        return $this->anneeDeces;
    }

    public function setAnneeDeces(?int $anneeDeces): static
    {
        $this->anneeDeces = $anneeDeces;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Enseignement>
     */
    public function getEnseignementsList(): Collection
    {
        return $this->enseignementsList;
    }

    public function addEnseignementsList(Enseignement $enseignement): static
    {
        if (!$this->enseignementsList->contains($enseignement)) {
            $this->enseignementsList->add($enseignement);
            $enseignement->setMaitre($this);
        }

        return $this;
    }

    public function removeEnseignementsList(Enseignement $enseignement): static
    {
        if ($this->enseignementsList->removeElement($enseignement)) {
            // set the owning side to null (unless already changed)
            if ($enseignement->getMaitre() === $this) {
                $enseignement->setMaitre(null);
            }
        }

        return $this;
    }
}

