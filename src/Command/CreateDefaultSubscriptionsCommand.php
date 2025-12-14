<?php

namespace App\Command;

use App\Entity\Subscription;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-default-subscriptions',
    description: 'Create FREE subscriptions for all users without a subscription',
)]
class CreateDefaultSubscriptionsCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating default FREE subscriptions for users');

        $users = $this->userRepository->findAll();
        $created = 0;
        $skipped = 0;

        $io->progressStart(count($users));

        foreach ($users as $user) {
            if ($user->getSubscription() === null) {
                $subscription = new Subscription();
                $subscription->setUser($user);
                $subscription->setPlanCode('FREE');
                $subscription->setStatus('active');
                $subscription->setStartedAt(new \DateTimeImmutable());

                $this->entityManager->persist($subscription);
                $created++;
            } else {
                $skipped++;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->newLine();
        $io->success([
            sprintf('Created %d default FREE subscriptions', $created),
            sprintf('Skipped %d users (already have a subscription)', $skipped),
            sprintf('Total users processed: %d', count($users)),
        ]);

        return Command::SUCCESS;
    }
}
