<?php

namespace App\Entity;

use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
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

    #[ORM\Column(type: 'string', enumType: TransactionType::class)]
    private TransactionType $type = TransactionType::WITHDRAWAL;

    #[ORM\ManyToOne(inversedBy: 'sendedTransactions')]
    private ?User $sender = null;

    #[ORM\ManyToOne(inversedBy: 'receivedTransactions')]
    private ?User $receiver = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public static function initBankDeposit(
        float $amount,
        float $totalAmount,
        ?float $fees = 0,
        ?User $receiver = null,
    ): self
    {
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setFees($fees);
        $transaction->setTotalAmount($totalAmount - $fees);
        $transaction->setType(TransactionType::DEPOSIT);
        $transaction->setReceiver($receiver);
        $transaction->setDescription('Rechargement par virement bancaire de ' . $amount . ' FCFA');

        return $transaction;
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

    public function getType(): ?TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
