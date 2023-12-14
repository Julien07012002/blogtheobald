<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:add-user',
    description: 'Créé un nouvel utilisateur'
)]
class AddUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Validator $validator,
        private UserRepository $users
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Configure la commande avec ses arguments et options.
     */
    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            ->addArgument('username', InputArgument::OPTIONAL, 'Le nom utilisateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Votre mot de passe')
            ->addArgument('email', InputArgument::OPTIONAL, 'Le mail')
            ->addArgument('full-name', InputArgument::OPTIONAL, 'Nom complet')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'si oui, lutilisateur sera créé en tant que Admin')
        ;
    }

    /**
     * Initialise la commande et crée une instance de SymfonyStyle pour les interactions avec l'utilisateur.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Interagit avec l'utilisateur pour obtenir les valeurs des arguments manquants.
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {

        // Cette fonction interagit avec l'utilisateur pour obtenir les arguments manquants,
        // tels que le nom d'utilisateur, le mot de passe, l'email et le nom complet.
        if (null !== $input->getArgument('username') && null !== $input->getArgument('password') && null !== $input->getArgument('email') && null !== $input->getArgument('full-name')) {
            return;
        }

        $this->io->title('AAjout d\'un utilisateur');
        $this->io->text([
            '',
            'Nous allons maintenant vous demander les éléments',
        ]);

        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Nom d\'Utilisateur</info>: '.$username);
        } else {
            $username = $this->io->ask('Nom d\'Utilisateur', null, [$this->validator, 'validateUsername']);
            $input->setArgument('username', $username);
        }

        $password = $input->getArgument('password');
        if (null !== $password) {
            $this->io->text(' > <info>Mot de passe</info>: '.u('*')->repeat(u($password)->length()));
        } else {
            $password = $this->io->askHidden('Mot de passe (votre saisi est invisible)', [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }

        $email = $input->getArgument('email');
        if (null !== $email) {
            $this->io->text(' > <info>Email</info>: '.$email);
        } else {
            $email = $this->io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }

        $fullName = $input->getArgument('full-name');
        if (null !== $fullName) {
            $this->io->text(' > <info>Nom Complet</info>: '.$fullName);
        } else {
            $fullName = $this->io->ask('Nom Complet', null, [$this->validator, 'validateFullName']);
            $input->setArgument('full-name', $fullName);
        }
    }


    // Cette fonction effectue la création d'un nouvel utilisateur en utilisant
    // les arguments fournis, valide les données, génère un mot de passe hashé,
    // et ajoute l'utilisateur en base de données.
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-user-command');

        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $email = $input->getArgument('email');
        $fullName = $input->getArgument('full-name');
        $isAdmin = $input->getOption('admin');

        $this->validateUserData($username, $plainPassword, $email, $fullName);

        $user = new User();
        $user->setFullName($fullName);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles([$isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('%s a été créé avec succès: %s (%s)', $isAdmin ? 'Utilisateur admin' : 'User', $user->getUsername(), $user->getEmail()));

        $event = $stopwatch->stop('add-user-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('Nouvel utilisateur dans la base de donnée avec l\'id: %d / Temps écoulé: %.2f ms / Mémoire consommée: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }


    // Cette fonction valide les données de l'utilisateur, telles que le nom d'utilisateur,
    // le mot de passe, l'email et le nom complet. Elle vérifie également s'il existe déjà
    // un utilisateur avec le même nom d'utilisateur ou la même adresse email en base de données.
    private function validateUserData($username, $plainPassword, $email, $fullName): void
    {
        $existingUser = $this->users->findOneBy(['username' => $username]);

        if (null !== $existingUser) {
            throw new RuntimeException(sprintf('Il y a déjà un utilisateur enregistré sous le nom "%s".', $username));
        }

        $this->validator->validatePassword($plainPassword);
        $this->validator->validateEmail($email);
        $this->validator->validateFullName($fullName);

        $existingEmail = $this->users->findOneBy(['email' => $email]);

        if (null !== $existingEmail) {
            throw new RuntimeException(sprintf('Il y a déjà un utilisateur enregistré avec "%s".', $email));
        }
    }

    // Cette fonction retourne un message d'aide qui explique comment utiliser la commande
    // et fournit des exemples d'utilisation avec ses arguments et options.
    private function getCommandHelp(): string
    {
        return <<<'HELP'
            La commande <info>%command.name%</info> crée de nouveaux utilisateurs et les enregistre dans la base de données :

            <info>php %command.full_name%</info> <comment>nom d'utilisateur mot de passe email</comment>

            Pour créé un utilisateur admin,
            ajouter l'option <comment>--admin</comment>

              <info>php %command.full_name%</info> nom d'utilisateur mot de passe email <comment>--admin</comment>

            La commande posera des questions si ils manquent des valeurs d'arguments :
      
              <info>php %command.full_name%</info> <comment>nom d'utilisateur mot de passe</comment>
           
              <info>php %command.full_name%</info> <comment>nom d'utilisateur</comment>

              <info>php %command.full_name%</info>
            HELP;
    }
}
