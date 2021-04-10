<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StockByCompanyRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=StockByCompanyRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class StockByCompany
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({"show_item", "list_items"})
     */
    private UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="stockByCompanies")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"show_item", "list_items"})
     */
    private Company $company;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="stockByCompanies")
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
    private int $stock;

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }
}
