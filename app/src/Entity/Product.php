<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({"show_item", "list_items"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"show_item", "list_items"})
     */
    private string $name;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"show_item", "list_items"})
     */
    private int $price;

    /**
     * @ORM\Column(type="float")
     *
     * @Groups({"show_item", "list_items"})
     */
    private float $tax;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="product", orphanRemoval=true)
     *
     * @Groups({"show_item"})
     * @var Collection|Transaction[]
     */
    private Collection $transactions;

    /**
     * @ORM\OneToMany(targetEntity=StockByCompany::class, mappedBy="product", orphanRemoval=true)
     *
     * @Groups({"show_item"})
     * @var Collection|StockByCompany[]
     */
    private Collection $stockByCompanies;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->stockByCompanies = new ArrayCollection();

        //hack because I decide to ignore tax but it should not be null
        $this->tax = 0;
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (! $this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setProduct($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getProduct() === $this) {
                $transaction->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StockByCompany[]
     */
    public function getStockByCompanies(): Collection
    {
        return $this->stockByCompanies;
    }

    public function addStockByCompany(StockByCompany $stockByCompany): self
    {
        if (! $this->stockByCompanies->contains($stockByCompany)) {
            $this->stockByCompanies[] = $stockByCompany;
            $stockByCompany->setProduct($this);
        }

        return $this;
    }

    public function removeStockByCompany(StockByCompany $stockByCompany): self
    {
        if ($this->stockByCompanies->removeElement($stockByCompany)) {
            // set the owning side to null (unless already changed)
            if ($stockByCompany->getProduct() === $this) {
                $stockByCompany->setProduct(null);
            }
        }

        return $this;
    }
}
