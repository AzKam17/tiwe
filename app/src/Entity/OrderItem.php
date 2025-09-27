<?php

namespace App\Entity;

use App\Enum\OrderItemEnum;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $master = null;

    #[ORM\Column(type: 'string', enumType: OrderItemEnum::class)]
    private ?OrderItemEnum $status = OrderItemEnum::PENDING;

    #[ORM\Column]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getMaster(): ?Order
    {
        return $this->master;
    }

    public function setMaster(?Order $master): static
    {
        $this->master = $master;

        return $this;
    }

    public function getStatus(): ?OrderItemEnum
    {
        return $this->status;
    }

    public function setStatus(OrderItemEnum $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
}
