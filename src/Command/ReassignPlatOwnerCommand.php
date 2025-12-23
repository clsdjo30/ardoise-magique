<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PlatCatalogue;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:plat:reassign-owner',
    description: 'Reassign all PlatCatalogue dishes to a specific user'
)]
class ReassignPlatOwnerCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'email',
            InputArgument::REQUIRED,
            'Email of the user who will own all dishes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        // Find user by email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('User with email "%s" not found.', $email));

            // Show available users
            $users = $this->entityManager->getRepository(User::class)->findAll();
            if (!empty($users)) {
                $io->section('Available users:');
                foreach ($users as $u) {
                    $io->text(sprintf('- %s', $u->getEmail()));
                }
            }

            return Command::FAILURE;
        }

        // Get all dishes
        $dishes = $this->entityManager->getRepository(PlatCatalogue::class)->findAll();

        if (empty($dishes)) {
            $io->warning('No dishes found in database.');
            return Command::SUCCESS;
        }

        $io->title(sprintf('Reassigning %d dishes to %s', count($dishes), $user->getEmail()));

        // Show current ownership
        $ownershipMap = [];
        foreach ($dishes as $dish) {
            $currentOwner = $dish->getOwner()?->getEmail() ?? 'No owner';
            if (!isset($ownershipMap[$currentOwner])) {
                $ownershipMap[$currentOwner] = 0;
            }
            $ownershipMap[$currentOwner]++;
        }

        $io->section('Current ownership:');
        foreach ($ownershipMap as $owner => $count) {
            $io->text(sprintf('- %s: %d dish(es)', $owner, $count));
        }

        // Confirm action
        if (!$io->confirm(sprintf('Are you sure you want to reassign all %d dishes to %s?', count($dishes), $user->getEmail()), false)) {
            $io->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Reassign all dishes
        $reassignedCount = 0;
        foreach ($dishes as $dish) {
            $dish->setOwner($user);
            $this->entityManager->persist($dish);
            $reassignedCount++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully reassigned %d dishes to %s', $reassignedCount, $user->getEmail()));

        return Command::SUCCESS;
    }
}
