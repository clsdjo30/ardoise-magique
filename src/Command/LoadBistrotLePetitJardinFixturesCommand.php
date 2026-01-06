<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\BistrotLePetitJardinFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures:load-bistrot-le-petit-jardin',
    description: 'Load only the Bistrot Le Petit Jardin fixtures.',
)]
class LoadBistrotLePetitJardinFixturesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BistrotLePetitJardinFixtures $fixtures,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Loading Bistrot Le Petit Jardin fixtures...');

        $this->fixtures->load($this->entityManager);

        $io->success('Bistrot Le Petit Jardin fixtures loaded.');

        return Command::SUCCESS;
    }
}
