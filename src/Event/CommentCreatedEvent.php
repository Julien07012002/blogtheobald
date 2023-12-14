<?php

namespace App\Event;

use App\Entity\Comment;
use Symfony\Contracts\EventDispatcher\Event;

class CommentCreatedEvent extends Event
{
    public function __construct(
        protected Comment $comment
    ) {
        // Le constructeur de l'événement CommentCreatedEvent prend un objet Comment en paramètre.
    }

    public function getComment(): Comment
    {
        // Cette méthode permet d'obtenir l'objet Comment associé à cet événement.
        return $this->comment;
    }
}