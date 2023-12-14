<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: 'comment')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'comment.blank')]
    #[Assert\Length(min: 5, minMessage: 'comment.too_short', max: 10000, maxMessage: 'comment.too_long')]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $publishedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
    }

    #[Assert\IsTrue(message: 'comment.is_spam')]
    public function isLegitComment(): bool
    {
        // Cette méthode vérifie si le commentaire est légitime en vérifiant s'il ne contient pas de caractères invalides, comme '@'.
        // Elle est annotée avec Assert\IsTrue, ce qui signifie que la condition renvoyant 'true' est une validation réussie.

        $containsInvalidCharacters = null !== u($this->content)->indexOf('@');

        // La méthode renvoie 'true' si le commentaire ne contient pas de caractères invalides.
        // Sinon, elle renvoie 'false', indiquant qu'il s'agit d'un spam.

        return !$containsInvalidCharacters;
    }

    public function getId(): ?int
    {
        // Cette méthode renvoie l'ID du commentaire, qui est une clé primaire auto-incrémentée.
        return $this->id;
    }

    public function getContent(): ?string
    {
        // Cette méthode renvoie le contenu du commentaire.
        return $this->content;
    }

    public function setContent(string $content): void
    {
        // Cette méthode permet de définir le contenu du commentaire avec la valeur fournie en argument.
        $this->content = $content;
    }

    public function getPublishedAt(): \DateTime
    {
        // Cette méthode renvoie la date et l'heure de publication du commentaire.
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        // Cette méthode permet de définir la date et l'heure de publication du commentaire avec la valeur fournie en argument.
        $this->publishedAt = $publishedAt;
    }

    public function getAuthor(): ?User
    {
        // Cette méthode renvoie l'auteur du commentaire, qui est une instance de l'entité User.
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        // Cette méthode permet de définir l'auteur du commentaire avec l'instance User fournie en argument.
        $this->author = $author;
    }

    public function getPost(): ?Post
    {
        // Cette méthode renvoie l'article (post) auquel le commentaire est associé.
        return $this->post;
    }

    public function setPost(Post $post): void
    {
        // Cette méthode permet de définir l'article (post) auquel le commentaire est associé avec l'instance Post fournie en argument.
        $this->post = $post;
    }
}