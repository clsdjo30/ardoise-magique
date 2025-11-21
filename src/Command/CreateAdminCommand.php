<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creer un utilisateur administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('restaurant_name', InputArgument::OPTIONAL, 'Nom du restaurant', 'Admin Restaurant')
            ->addOption('super-admin', null, InputOption::VALUE_NONE, 'Creer un super-administrateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $restaurantName = $input->getArgument('restaurant_name');
        $isSuperAdmin = $input->getOption('super-admin');

        // Verifier si l'utilisateur existe deja
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            $io->error(sprintf('Un utilisateur avec l\'email "%s" existe deja.', $email));
            return Command::FAILURE;
        }

        // Creer le nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setNomRestaurant($restaurantName);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Definir les roles
        if ($isSuperAdmin) {
            $user->setRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);
            $io->note('Creation d\'un super-administrateur');
        } else {
            $user->setRoles(['ROLE_USER']);
            $io->note('Creation d\'un utilisateur standard');
        }

        // Sauvegarder l'utilisateur
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success([
            'Utilisateur cree avec succes !',
            sprintf('Email: %s', $email),
            sprintf('Restaurant: %s', $restaurantName),
            sprintf('Slug: %s', $user->getSlug()),
            sprintf('Roles: %s', implode(', ', $user->getRoles())),
        ]);

        if ($isSuperAdmin) {
            $io->warning('Cet utilisateur a acces a tous les menus de tous les restaurants.');
        }

        return Command::SUCCESS;
    }
}
