<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmployeeRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EmployeeRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Employee
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
     * @ORM\Column(type="date")
     *
     * @Groups({"show_item", "list_items"})
     */
    private DateTimeInterface $birthday;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"show_item", "list_items"})
     */
    private string $country;

    /**
     * @ORM\Column(type="date")
     *
     * @Groups({"show_item", "list_items"})
     */
    private DateTimeInterface $firstDayInTheCompany;

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

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

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

    public function getFirstDayInTheCompany(): ?DateTimeInterface
    {
        return $this->firstDayInTheCompany;
    }

    public function setFirstDayInTheCompany(DateTimeInterface $firstDayInTheCompany): self
    {
        $this->firstDayInTheCompany = $firstDayInTheCompany;

        return $this;
    }
}
