<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckRequirementsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        // Le constructeur prend l'EntityManagerInterface en tant que dépendance.
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => 'handleConsoleError',
            KernelEvents::EXCEPTION => 'handleKernelException',
        ];
        // Cette méthode statique déclare les événements auxquels cet abonné doit répondre.
    }

    public function handleConsoleError(ConsoleErrorEvent $event): void
    {
        $commandNames = ['doctrine:fixtures:load', 'doctrine:database:create', 'doctrine:schema:create', 'doctrine:database:drop'];

        if ($event->getCommand() && \in_array($event->getCommand()->getName(), $commandNames, true)) {
            // Cette méthode est appelée en cas d'erreur de console pour certaines commandes.

            if ($this->isSQLitePlatform() && !\extension_loaded('sqlite3')) {
                // Vérifie si la plateforme de la base de données est SQLite et si l'extension 'sqlite3' est chargée.

                $io = new SymfonyStyle($event->getInput(), $event->getOutput());
                $io->error('This command requires to have the "sqlite3" PHP extension enabled because, by default, the Symfony Demo application uses SQLite to store its information.');
                // En cas de manquement de l'extension 'sqlite3', un message d'erreur est affiché.
            }
        }
    }

    public function handleKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $previousException = $exception->getPrevious();

        $isDriverException = ($exception instanceof DriverException || $previousException instanceof DriverException);

        if ($isDriverException && $this->isSQLitePlatform() && !\extension_loaded('sqlite3')) {
            // Cette méthode est appelée en cas d'exception du noyau (KernelException).

            $event->setThrowable(new \Exception('PHP extension "sqlite3" must be enabled because, by default, the Symfony Demo application uses SQLite to store its information.'));
            // Si l'exception est une DriverException et que la plateforme de la base de données est SQLite sans extension 'sqlite3', une exception personnalisée est définie.
        }
    }

    private function isSQLitePlatform(): bool
    {
        $databasePlatform = $this->entityManager->getConnection()->getDatabasePlatform();
        // Vérifie la plateforme de la base de données utilisée dans l'application.

        return $databasePlatform ? 'sqlite' === $databasePlatform->getName() : false;
        // Retourne vrai si la plateforme est SQLite, sinon faux.
    }
}