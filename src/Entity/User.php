<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['posts.show'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(groups: ['posts.show'])]
    private ?string $fullName = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFullName(string $fullName): void
    {
        // Cette méthode permet de définir le nom complet de l'utilisateur.
        $this->fullName = $fullName;
    }

    public function getFullName(): ?string
    {
        // Cette méthode renvoie le nom complet de l'utilisateur.
        return $this->fullName;
    }

    public function getUserIdentifier(): string
    {
        // Cette méthode renvoie un identifiant unique de l'utilisateur,
        // généralement son nom d'utilisateur ou son adresse e-mail.
        return $this->username;
    }

    public function getUsername(): string
    {
        // Cette méthode renvoie le nom d'utilisateur de l'utilisateur.
        // Elle est appelée par Symfony lors de l'authentification.
        return $this->getUserIdentifier();
    }

    public function setUsername(string $username): void
    {
        // Cette méthode permet de définir le nom d'utilisateur de l'utilisateur.
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        // Cette méthode renvoie l'adresse e-mail de l'utilisateur.
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        // Cette méthode permet de définir l'adresse e-mail de l'utilisateur.
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        // Cette méthode renvoie le mot de passe de l'utilisateur.
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        // Cette méthode permet de définir le mot de passe de l'utilisateur.
        $this->password = $password;
    }


    public function getRoles(): array
    {
        // Cette méthode renvoie les rôles de l'utilisateur.
        $roles = $this->roles;

        // Garantit qu'un utilisateur a toujours au moins un rôle pour des raisons de sécurité.
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        // Cette méthode permet de définir les rôles de l'utilisateur.
        $this->roles = $roles;
    }


    public function getSalt(): ?string
    {
        // Nous utilisons bcrypt dans security.yaml pour encoder le mot de passe,
        // donc la valeur du sel (salt) est intégrée donc pas besoin d'en générer.
        return null;
    }

    public function eraseCredentials(): void
    {
        // Cette méthode est appelée pour supprimer les données sensibles de l'utilisateur.
        // Par exemple, si vous aviez une propriété plainPassword, vous la nullifieriez ici.
        // $this->plainPassword = null;
    }

    public function __serialize(): array
    {
        // Cette méthode est appelée pour sérialiser l'objet lorsqu'il est stocké en session.
        // Elle renvoie les données de l'utilisateur qui doivent être sérialisées.
        return [$this->id, $this->username, $this->password];
    }

    public function __unserialize(array $data): void
    {
        // Cette méthode est appelée lors de la désérialisation de l'objet depuis la session.
        // Elle restaure les données de l'utilisateur à partir des données sérialisées.
        [$this->id, $this->username, $this->password] = $data;
    }
}