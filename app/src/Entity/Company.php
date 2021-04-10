<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Throwable;

/**
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Company
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
     * @ORM\Column(type="bigint")
     *
     * @Groups({"show_item", "list_items"})
     */
    private int $balance;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"show_item", "list_items"})
     */
    private string $country;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="company", orphanRemoval=true)
     *
     * @Groups({"show_item"})
     * @var Collection|Transaction[]
     */
    private Collection $transactions;

    /**
     * @ORM\OneToMany(targetEntity=StockByCompany::class, mappedBy="company", orphanRemoval=true)
     *
     * @Groups({"show_item"})
     * @var Collection|StockByCompany[]
     */
    private Collection $stockByCompanies;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->stockByCompanies = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     *
     * @throws Throwable;
     */
    public function onPrePersist(): void
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
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

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

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
            $transaction->setCompany($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCompany() === $this) {
                $transaction->setCompany(null);
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
            $stockByCompany->setCompany($this);
        }

        return $this;
    }

    public function removeStockByCompany(StockByCompany $stockByCompany): self
    {
        if ($this->stockByCompanies->removeElement($stockByCompany)) {
            // set the owning side to null (unless already changed)
            if ($stockByCompany->getCompany() === $this) {
                $stockByCompany->setCompany(null);
            }
        }

        return $this;
    }
}
