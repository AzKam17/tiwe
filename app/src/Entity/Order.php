<?php

namespace App\Entity;

use App\Enum\OrderItemEnum;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?float $fees = null;

    #[ORM\Column]
    private ?float $totalAmount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'master')]
    private Collection $items;

    #[ORM\Column(type: 'string', enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::PENDING;

    #[ORM\Column(length: 6, unique: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function updateStatusBasedOnItems(): void
    {
        $allDelivered = true;
        $anyCanceled = true;

        foreach ($this->items as $item) {
            if ($item->getStatus() !== OrderItemEnum::DELIVERED) {
                $allDelivered = false;
            }
            if ($item->getStatus() !== OrderItemEnum::CANCELED) {
                $anyCanceled = false;
            }
        }

        if ($anyCanceled) {
            $this->status = OrderStatus::CANCELED;
        } elseif ($allDelivered) {
            $this->status = OrderStatus::DELIVERED;
        } else {
            $this->status = OrderStatus::PENDING;
        }
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function computeAmount(): static
    {
        $total = array_reduce(
            $this->items->toArray(),
            fn($sum, OrderItem $item) => $sum + ($item->getPrice() * $item->getQuantity()),
            0
        );
        $this->setAmount($total);
        return $this;
    }

    public function getFees(): ?float
    {
        return $this->fees;
    }

    public function setFees(float $fees): static
    {
        $this->fees = $fees;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function computeTotalAmount(): static
    {
        $this->setTotalAmount(($this->amount ?? 0) + ($this->fees ?? 0));
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = CarbonImmutable::now()->toDateTimeImmutable();
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = CarbonImmutable::now()->toDateTimeImmutable();
        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setMaster($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getMaster() === $this) {
                $item->setMaster(null);
            }
        }

        return $this;
    }

    public function addItems(array $items): static
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    public function emptyItems(): static
    {
        foreach ($this->items as $item) {
            $this->removeItem($item);
        }
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @throws RandomException
     */
    #[ORM\PrePersist]
    public function generateSlug(): void
    {
        if ($this->slug === null) {
            $this->slug = $this->generateRandomSlug(6);
        }
    }

    /**
     * Generate a random string with lowercase letters and numbers
     * @throws RandomException
     */
    private function generateRandomSlug(int $length = 6): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $slug = '';
        for ($i = 0; $i < $length; $i++) {
            $slug .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $slug;
    }
}
