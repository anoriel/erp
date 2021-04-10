<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Safe\DateTime;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
  /**
   * @ORM\Id
   * @ORM\Column(type="uuid", unique=true)
   *
   * @Groups({"show_item", "list_items"})
   */
    private UuidInterface $id;

  /**
   * @ORM\Column(name="login", type="string", unique=true)
   *
   * @Groups({"show_item", "list_items"})
   * @Assert\NotBlank()
   */
    private string $login;

  /**
   * @Groups({"show_item", "list_items"})
   * @Assert\NotBlank()
   * @Assert\Length(max=4096)
   */
    private ?string $plainPassword = null;

  /**
   * @ORM\Column(name="password", type="string")
   *
   * @Groups({"show_item", "list_items"})
   */
    private ?string $password = null;

  /**
   * @ORM\Column(name="roles", type="simple_array")
   *
   * @Groups({"show_item", "list_items"})
   * @var string[]
   */
    private array $roles;

  /**
   * @ORM\Column(name="created", type="safe_datetime")
   *
   * @Groups({"show_item", "list_items"})
   */
    private DateTime $created;

  /**
   * @ORM\Column(name="updated", type="safe_datetime", nullable=true)
   *
   * @Groups({"show_item", "list_items"})
   */
    private DateTime $updated;

    public function __construct()
    {
        $this->roles = [];
    }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
    public function onPrePersist(): void
    {
        $this->id = Uuid::uuid4();
        $this->created = new DateTime('NOW');
    }

  /**
   * @ORM\PreUpdate
   */
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('NOW');
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getUsername(): string
    {
        return $this->login;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): void
    {
        $this->plainPassword = $password;

      // forces the object to look "dirty" to Doctrine. Avoids
      // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

  /**
   * @return null
   */
    public function getSalt()
    {
      // The bcrypt algorithm doesn't require a separate salt.
        return null;
    }

  /**
   * @return string[]
   */
    public function getRoles(): array
    {
        return $this->roles;
    }

  /**
   * @param string[] $roles
   */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }
}
