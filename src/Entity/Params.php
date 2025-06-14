<?php

namespace App\Entity;

use App\Repository\ParamsRepository;
use App\Traits\StateEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ParamsRepository::class)]
class Params
{
    use StateEntity;
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $useTerms = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $realId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUseTerms(): ?string
    {
        return $this->useTerms;
    }

    public function setUseTerms(?string $useTerms): self
    {
        $this->useTerms = $useTerms;

        return $this;
    }

    public function getRealId():  ?string
    {
        return $this->realId;
    }

    public function setRealId(?string $realId): self
    {
        $this->realId = $realId;

        return $this;
    }

    public function init(): void
    {
        $this->setDeleted(false);
        $this->setValid(true);
        $this->setUseTerms("vide");
    }
}
