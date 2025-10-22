<?php

namespace App\Command\UserManagement;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('users:create:admin')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $emi,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('language', InputArgument::OPTIONAL, 'Language')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $style->title('Creating admin user...');

        $user = (new User())
            ->setUsername($input->getArgument('username'))
            ->setEmail($input->getArgument('email'))
            ->setLanguage($input->getArgument('language') ?? 'en_US')
            ->addRole('ROLE_ADMIN')
        ;

        try {
            $this->emi->persist($user);
            $this->emi->flush();
        } catch (\Exception $e) {
            $style->error('Failed to create user:');
            $style->error($e->getMessage());

            return Command::FAILURE;
        }

        $style->info('User created! You should receive an email to login!');

        return Command::SUCCESS;
    }
}
