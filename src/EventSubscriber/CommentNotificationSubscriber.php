<?php


namespace App\EventSubscriber;

use App\Event\CommentCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private string $sender
    ) {
        // Le constructeur prend plusieurs dépendances, notamment le service de messagerie, le générateur d'URL, le traducteur et l'adresse de l'expéditeur.
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
        // Cette méthode statique déclare que cette classe est un abonné à l'événement CommentCreatedEvent.
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();
        $post = $comment->getPost();

        $linkToPost = $this->urlGenerator->generate('blog_post', [
            'slug' => $post->getSlug(),
            '_fragment' => 'comment_'.$comment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        // Génère un lien vers le post avec un fragment pointant vers le nouveau commentaire.

        $subject = $this->translator->trans('notification.comment_created');
        $body = $this->translator->trans('notification.comment_created.description', [
            'title' => $post->getTitle(),
            'link' => $linkToPost,
        ]);
        // Traduit les messages de notification pour l'email.

        $email = (new Email())
            ->from($this->sender)
            ->to($post->getAuthor()->getEmail())
            ->subject($subject)
            ->html($body);
        // Crée un email avec l'expéditeur, le destinataire, le sujet et le contenu HTML.

        $this->mailer->send($email);
        // Envoie l'email.
    }
}