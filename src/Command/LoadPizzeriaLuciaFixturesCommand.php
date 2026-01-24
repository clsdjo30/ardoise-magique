<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\PizzeriaLuciaFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures:load-pizzeria-lucia',
    description: 'Load only the Pizzeria Lucia fixtures.',
)]
class LoadPizzeriaLuciaFixturesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PizzeriaLuciaFixtures $fixtures,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note('Loading Pizzeria Lucia fixtures...');

        $this->fixtures->load($this->entityManager);

        $io->success('Pizzeria Lucia fixtures loaded.');

        return Command::SUCCESS;
    }
}
