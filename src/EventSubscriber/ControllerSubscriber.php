<?php


namespace App\EventSubscriber;

use App\Twig\SourceCodeExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SourceCodeExtension $twigExtension
    ) {
        // Le constructeur prend une dépendance, qui est l'extension Twig utilisée pour afficher le code source des contrôleurs.
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'registerCurrentController',
        ];
        // Cette méthode statique déclare que cette classe est un abonné à l'événement KernelEvents::CONTROLLER.
    }

    public function registerCurrentController(ControllerEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->twigExtension->setController($event->getController());
        }
        // Lorsqu'un événement ControllerEvent est déclenché, cette méthode est appelée.
        // Si la requête est la requête principale, elle enregistre le contrôleur actuel à l'aide de l'extension Twig.
    }
}
