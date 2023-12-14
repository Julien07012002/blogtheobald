<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[AsCommand(
    name: 'app:list-users',
    description: 'Liste de tous les utilisateurs existants',
    aliases: ['app:users']
)]
class ListUsersCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private string $emailSender,
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
            ->addOption('max-results', null, InputOption::VALUE_OPTIONAL, 'Limite le nombre d\'utilisateurs répertoriés', 50)
            ->addOption('send-to', null, InputOption::VALUE_OPTIONAL, 'S\'il est défini, le résultat est envoyé à l\'adresse e-mail indiquée')
        ;
    }

     /**
     * Exécute la commande et liste les utilisateurs en fonction des options fournies.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maxResults = $input->getOption('max-results');
        $allUsers = $this->users->findBy([], ['id' => 'DESC'], $maxResults);

        $usersAsPlainArrays = array_map(static function (User $user) {
            return [
                $user->getId(),
                $user->getFullName(),
                $user->getUsername(),
                $user->getEmail(),
                implode(', ', $user->getRoles()),
            ];
        }, $allUsers);

        $bufferedOutput = new BufferedOutput();
        $io = new SymfonyStyle($input, $bufferedOutput);
        $io->table(
            ['ID', 'Full Name', 'Username', 'Email', 'Roles'],
            $usersAsPlainArrays
        );

        $usersAsATable = $bufferedOutput->fetch();
        $output->write($usersAsATable);

        if (null !== $email = $input->getOption('send-to')) {
            $this->sendReport($usersAsATable, $email);
        }

        return Command::SUCCESS;
    }

    /**
     * Envoie le rapport à l'adresse e-mail spécifiée.
     */
    private function sendReport(string $contents, string $recipient): void
    {
        $email = (new Email())
            ->from($this->emailSender)
            ->to($recipient)
            ->subject(sprintf('app:list-users report (%s)', date('Y-m-d H:i:s')))
            ->text($contents);

        $this->mailer->send($email);
    }
}
