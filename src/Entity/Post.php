<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'post')]
#[UniqueEntity(fields: ['slug'], errorPath: 'title', message: 'post.slug_unique')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(groups: ['posts.show'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(groups: ['posts.show'])]
    private ?string $title = null;

    #[ORM\Column(type: 'string')]
    #[Groups(groups: ['posts.show'])]
    private ?string $slug = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'post.blank_summary')]
    #[Assert\Length(max: 255)]
    #[Groups(groups: ['posts.show'])]
    private ?string $summary = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'post.blank_content')]
    #[Assert\Length(min: 10, minMessage: 'post.too_short_content')]
    #[Groups(groups: ['posts.show'])]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(groups: ['posts.show'])]
    private \DateTime $publishedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['posts.show'])]
    private ?User $author = null;

    /**
     * @var Comment[]|Collection
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['publishedAt' => 'DESC'])]
    private Collection $comments;

    /**
     * @var Tag[]|Collection
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'post_tag')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Assert\Count(max: 4, maxMessage: 'post.too_many_tags')]
    #[Groups(groups: ['posts.show'])]
    private Collection $tags;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        // Cette méthode renvoie l'ID du post, qui est une clé primaire auto-incrémentée.
        return $this->id;
    }

    public function getTitle(): ?string
    {
        // Cette méthode renvoie le titre du post.
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        // Cette méthode permet de définir le titre du post avec la valeur fournie en argument.
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        // Cette méthode renvoie le slug du post.
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        // Cette méthode permet de définir le slug du post avec la valeur fournie en argument.
        $this->slug = $slug;
    }

    public function getContent(): ?string
    {
        // Cette méthode renvoie le contenu du post.
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        // Cette méthode permet de définir le contenu du post avec la valeur fournie en argument.
        $this->content = $content;
    }

    public function getPublishedAt(): \DateTime
    {
        // Cette méthode renvoie la date et l'heure de publication du post.
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        // Cette méthode permet de définir la date et l'heure de publication du post avec la valeur fournie en argument.
        $this->publishedAt = $publishedAt;
    }

    public function getAuthor(): ?User
    {
        // Cette méthode renvoie l'auteur du post, qui est une instance de l'entité User.
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        // Cette méthode permet de définir l'auteur du post avec l'instance User fournie en argument.
        $this->author = $author;
    }

    public function getComments(): Collection
    {
        // Cette méthode renvoie une collection de commentaires associés à ce post.
        return $this->comments;
    }

    public function addComment(Comment $comment): void
    {
        // Cette méthode permet d'ajouter un commentaire à la collection de commentaires associés à ce post.
        // Elle définit également ce post comme le post parent du commentaire.
        $comment->setPost($this);
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        // Cette méthode permet de supprimer un commentaire de la collection de commentaires associés à ce post.
        $this->comments->removeElement($comment);
    }

    public function getSummary(): ?string
    {
        // Cette méthode renvoie le résumé du post.
        return $this->summary;
    }

    public function setSummary(?string $summary): void
    {
        // Cette méthode permet de définir le résumé du post avec la valeur fournie en argument.
        $this->summary = $summary;
    }

    public function addTag(Tag ...$tags): void
    {
        // Cette méthode permet d'ajouter des tags au post.
        // Elle vérifie d'abord si le tag n'est pas déjà associé au post pour éviter les doublons.
        foreach ($tags as $tag) {
            if (!$this->tags->contains($tag)) {
                $this->tags->add($tag);
            }
        }
    }

    public function removeTag(Tag $tag): void
    {
        // Cette méthode permet de supprimer un tag du post.
        $this->tags->removeElement($tag);
    }

    public function getTags(): Collection
    {
        // Cette méthode renvoie une collection de tags associés à ce post.
        return $this->tags;
    }

    private array $en_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    private array $fr_days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

    private array $en_months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    private array $fr_months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];

    #[Groups(groups: ['posts.show'])]
    public function getPublishedAtLocal(string $format = 'l j F Y'): string
    {

        // Cette méthode prend en compte la date de publication du post,
        // puis la formate en utilisant le format fourni en argument (par défaut 'l j F Y').
        // Elle remplace ensuite les noms des jours et des mois en anglais par leurs équivalents en français
        // pour obtenir une date locale en français.

        // Par exemple, si $this->publishedAt est un lundi 4 octobre 2023, cette méthode renverra "Lundi 4 Octobre 2023".
        // Si le format par défaut est utilisé, sinon, elle respectera le format personnalisé fourni.
        return str_replace(
            $this->en_months,
            $this->fr_months,
            str_replace(
                $this->en_days,
                $this->fr_days,
                $this->publishedAt->format($format)
            )
        );
    }
}
