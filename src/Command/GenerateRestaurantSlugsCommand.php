<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-restaurant-slugs',
    description: 'Generate slugs for all restaurants without a slug',
)]
class GenerateRestaurantSlugsCommand extends Command
{
    public function __construct(
        private readonly RestaurantRepository $restaurantRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating slugs for restaurants');

        $restaurants = $this->restaurantRepository->findAll();
        $generated = 0;
        $skipped = 0;

        $io->progressStart(count($restaurants));

        foreach ($restaurants as $restaurant) {
            if ($restaurant->getSlug() === null) {
                $restaurant->generateSlug();
                $generated++;
            } else {
                $skipped++;
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->newLine();
        $io->success([
            sprintf('Generated %d restaurant slugs', $generated),
            sprintf('Skipped %d restaurants (already have a slug)', $skipped),
            sprintf('Total restaurants processed: %d', count($restaurants)),
        ]);

        return Command::SUCCESS;
    }
}
