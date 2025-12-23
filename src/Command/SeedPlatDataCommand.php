<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PlatCategorie;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:plat-data',
    description: 'Seed PlatCategorie and Tag tables with default French cuisine data'
)]
class SeedPlatDataCommand extends Command
{
    private const CATEGORIES = [
        'Entrées',
        'Plats principaux',
        'Desserts',
        'Fromages',
        'Boissons',
        'Vins',
        'Apéritifs',
        'Digestifs',
        'Cafés & Thés',
        'Amuse-bouches',
        'Soupes & Potages',
        'Salades',
        'Pâtes & Risottos',
        'Viandes',
        'Poissons & Fruits de mer',
    ];

    private const TAGS = [
        'Sans gluten',
        'Végétarien',
        'Végétalien',
        'Vegan',
        'Pimenté',
        'Épicé',
        'Fait maison',
        'Bio',
        'Allergène: Arachides',
        'Allergène: Fruits à coque',
        'Allergène: Lait',
        'Allergène: Œufs',
        'Allergène: Poisson',
        'Allergène: Crustacés',
        'Sans lactose',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force seeding even if data already exists'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        // Check if data already exists
        $existingCategories = $this->entityManager->getRepository(PlatCategorie::class)->count([]);
        $existingTags = $this->entityManager->getRepository(Tag::class)->count([]);

        if (($existingCategories > 0 || $existingTags > 0) && !$force) {
            $io->warning('Data already exists in database. Use --force to override and add more data.');
            $io->note(sprintf('Existing categories: %d', $existingCategories));
            $io->note(sprintf('Existing tags: %d', $existingTags));
            return Command::FAILURE;
        }

        $io->title('Seeding PlatCategorie and Tag data');

        // Seed Categories
        $io->section('Creating PlatCategorie entries');
        $categoriesCreated = 0;

        foreach (self::CATEGORIES as $titre) {
            // Check if already exists
            $existing = $this->entityManager->getRepository(PlatCategorie::class)
                ->findOneBy(['titre' => $titre]);

            if ($existing) {
                $io->text(sprintf('⏭️  Category "%s" already exists, skipping', $titre));
                continue;
            }

            $category = new PlatCategorie();
            $category->setTitre($titre);

            $this->entityManager->persist($category);
            $categoriesCreated++;

            $io->text(sprintf('✓ Created category: %s', $titre));
        }

        // Seed Tags
        $io->section('Creating Tag entries');
        $tagsCreated = 0;

        foreach (self::TAGS as $label) {
            // Check if already exists
            $existing = $this->entityManager->getRepository(Tag::class)
                ->findOneBy(['label' => $label]);

            if ($existing) {
                $io->text(sprintf('⏭️  Tag "%s" already exists, skipping', $label));
                continue;
            }

            $tag = new Tag();
            $tag->setLabel($label);

            $this->entityManager->persist($tag);
            $tagsCreated++;

            $io->text(sprintf('✓ Created tag: %s', $label));
        }

        // Flush all changes
        $this->entityManager->flush();

        // Summary
        $io->newLine();
        $io->success([
            sprintf('Seeding completed successfully!'),
            sprintf('Categories created: %d', $categoriesCreated),
            sprintf('Tags created: %d', $tagsCreated),
        ]);

        return Command::SUCCESS;
    }
}
