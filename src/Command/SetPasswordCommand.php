<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('users:set-password')]
class SetPasswordCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $emi,
        private readonly UserRepository $repository,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'The password to set for the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        $style = new SymfonyStyle($input, $output);
        /** @var User|null $user */
        $user = $this->repository->find($email);

        if (!$user) {
            $style->error('No user found for the email address '.$email);

            return Command::FAILURE;
        }

        $newPassword = $this->hasher->hashPassword($user, $input->getArgument('password'));
        $user->setPassword($newPassword);

        $this->emi->persist($user);
        $this->emi->flush();

        $style->success('Password updated !');

        return Command::SUCCESS;
    }
}
