<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'tag')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['posts.show'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Groups(groups: ['posts.show'])]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        // Cette méthode permet de définir le nom du tag.
        $this->name = $name;
    }

    public function getName(): ?string
    {
        // Cette méthode renvoie le nom du tag.
        return $this->name;
    }

    public function __toString(): string
    {
        // Cette méthode magique renvoie une représentation sous forme de chaîne de caractères
        // de l'objet Tag. Dans ce cas, elle renvoie simplement le nom du tag.
        return $this->name;
    }
}