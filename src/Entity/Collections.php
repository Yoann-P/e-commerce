<?php

namespace App\Entity;

use App\Repository\CollectionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionsRepository::class)]
class Collections
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $button_text = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $button_link = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $uptated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isMega = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getButtonText(): ?string
    {
        return $this->button_text;
    }

    public function setButtonText(?string $button_text): static
    {
        $this->button_text = $button_text;

        return $this;
    }

    public function getButtonLink(): ?string
    {
        return $this->button_link;
    }

    public function setButtonLink(?string $button_link): static
    {
        $this->button_link = $button_link;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getUptatedAt(): ?\DateTimeImmutable
    {
        return $this->uptated_at;
    }

    public function setUptatedAt(?\DateTimeImmutable $uptated_at): static
    {
        $this->uptated_at = $uptated_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isIsMega(): ?bool
    {
        return $this->isMega;
    }

    public function setIsMega(?bool $isMega): static
    {
        $this->isMega = $isMega;

        return $this;
    }
}
