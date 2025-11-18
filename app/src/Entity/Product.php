<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?ProductCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $measurementType = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $measurementUnit = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $images = [];

    #[ORM\OneToMany(targetEntity: InventoryEntry::class, mappedBy: 'product')]
    private Collection $inventoryEntries;

    public function __construct()
    {
        $this->inventoryEntries = new ArrayCollection();
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

    public function getCategory(): ?ProductCategory
    {
        return $this->category;
    }

    public function setCategory(?ProductCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

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

    public function getMeasurementType(): ?string
    {
        return $this->measurementType;
    }

    public function setMeasurementType(?string $measurementType): static
    {
        $this->measurementType = $measurementType;

        return $this;
    }

    public function getMeasurementUnit(): ?string
    {
        return $this->measurementUnit;
    }

    public function setMeasurementUnit(?string $measurementUnit): static
    {
        $this->measurementUnit = $measurementUnit;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @return Collection<int, InventoryEntry>
     */
    public function getInventoryEntries(): Collection
    {
        return $this->inventoryEntries;
    }

    public function addInventoryEntry(InventoryEntry $inventoryEntry): static
    {
        if (!$this->inventoryEntries->contains($inventoryEntry)) {
            $this->inventoryEntries->add($inventoryEntry);
            $inventoryEntry->setProduct($this);
        }

        return $this;
    }

    public function removeInventoryEntry(InventoryEntry $inventoryEntry): static
    {
        if ($this->inventoryEntries->removeElement($inventoryEntry)) {
            // set the owning side to null (unless already changed)
            if ($inventoryEntry->getProduct() === $this) {
                $inventoryEntry->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * Get total quantity in inventory
     */
    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->inventoryEntries as $entry) {
            $total += $entry->getQuantity();
        }
        return $total;
    }

    /**
     * Get the latest price from inventory entries
     */
    public function getLatestPrice(): ?float
    {
        if ($this->inventoryEntries->isEmpty()) {
            return null;
        }

        // Get the most recent entry
        $latestEntry = null;
        foreach ($this->inventoryEntries as $entry) {
            if ($latestEntry === null || $entry->getCreatedAt() > $latestEntry->getCreatedAt()) {
                $latestEntry = $entry;
            }
        }

        return $latestEntry ? (float)$latestEntry->getPrice() : null;
    }

    /**
     * Get total inventory value
     */
    public function getTotalInventoryValue(): float
    {
        $total = 0;
        foreach ($this->inventoryEntries as $entry) {
            $total += $entry->getTotalPrice();
        }
        return $total;
    }

    /**
     * Get the display image for a specific user
     * Returns custom image from user's latest entry, or product default image
     */
    public function getDisplayImageForUser(?User $user): ?string
    {
        if ($user === null) {
            // No user provided, return default product image
            return !empty($this->images) && isset($this->images[0]) ? $this->images[0] : null;
        }

        // Find the latest inventory entry with a custom image for this user
        $latestEntryWithImage = null;
        foreach ($this->inventoryEntries as $entry) {
            if ($entry->getUser() === $user && $entry->getImage()) {
                if ($latestEntryWithImage === null || $entry->getCreatedAt() > $latestEntryWithImage->getCreatedAt()) {
                    $latestEntryWithImage = $entry;
                }
            }
        }

        // If user has uploaded a custom image, use it
        if ($latestEntryWithImage && $latestEntryWithImage->getImage()) {
            return $latestEntryWithImage->getImage();
        }

        // Otherwise, fall back to product default image
        return !empty($this->images) && isset($this->images[0]) ? $this->images[0] : null;
    }
}
