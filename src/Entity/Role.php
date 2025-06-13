<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $roleName = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: 'json')]
    private array $sections = [];

    public function getId(): ?int { return $this->id; }
    public function getRoleName(): ?string { return $this->roleName; }
    public function setRoleName(string $roleName): self { $this->roleName = $roleName; return $this; }
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }
    public function getSections(): array { return $this->sections; }
    public function setSections(array $sections): self { $this->sections = $sections; return $this; }
}