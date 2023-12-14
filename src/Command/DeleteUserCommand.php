<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'app:delete-user',
    description: 'Supprime un utilisateur existant de la base de données'
)]
class DeleteUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Validator $validator,
        private UserRepository $users,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    
    /**
     * Configure la commande avec ses arguments et options.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Le nom d\'un utilisateur existant');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Interagit avec l'utilisateur pour obtenir le nom d'utilisateur.
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('username')) {
            return;
        }

        $this->io->title('Suppression d\'un utilisateur');
        $this->io->text([
            'Nous allons maintenant vous demander la valeur de tous les arguments de commande manquants.',
            '',
        ]);

        $username = $this->io->ask('Nom d\'utilisateur', null, [$this->validator, 'validateUsername']);
        $input->setArgument('username', $username);
    }

    /**
     * Exécute la commande et supprime un utilisateur en fonction du nom d'utilisateur fourni.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $this->validator->validateUsername($input->getArgument('username'));

        /** @var User|null $user */
        $user = $this->users->findOneByUsername($username);

        if (null === $user) {
            throw new RuntimeException(sprintf('Le nom d\'utilisateur avec le nom "%s" n\'est pas trouvé', $username));
        }


        $userId = $user->getId();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $userUsername = $user->getUsername();
        $userEmail = $user->getEmail();

        $this->io->success(sprintf('L\'utilisateur "%s" (ID: %d, email: %s) à correctement été supprimé', $userUsername, $userId, $userEmail));


        $this->logger->info('L\'utilisateur "{username}" (ID: {id}, email: {email}) a été supprimé avec succès.', ['username' => $userUsername, 'id' => $userId, 'email' => $userEmail]);

        return Command::SUCCESS;
    }
}
