<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Transaction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({"show_item", "list_items"})
     */
    private UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"show_item", "list_items"})
     */
    private Company $company;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="transactions")
     *
     * @Groups({"show_item", "list_items"})
     */
    private ?Customer $customer = null;

    /**
     * @ORM\ManyToOne(targetEntity=Provider::class, inversedBy="transactions")
     *
     * @Groups({"show_item", "list_items"})
     */
    private ?Provider $provider = null;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"show_item", "list_items"})
     */
    private Product $product;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"show_item", "list_items"})
     */
    private int $quantity;

    /**
     * @ORM\PrePersist
     *
     * @throws Exception;
     */
    public function onPrePersist(): void
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}
