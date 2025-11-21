<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des Utilisateurs')
            ->setPageTitle('new', 'Créer un Utilisateur')
            ->setPageTitle('edit', 'Modifier l\'Utilisateur')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield EmailField::new('email', 'Email');

        yield TextField::new('nom_restaurant', 'Nom du restaurant');

        yield TextField::new('slug', 'Slug (URL)')
            ->hideOnForm()
            ->setHelp('Généré automatiquement à partir du nom du restaurant');

        yield ArrayField::new('roles', 'Rôles')
            ->setHelp('Gérez les rôles de l\'utilisateur (ROLE_USER, ROLE_SUPER_ADMIN)');

        // Note: Le mot de passe ne peut pas être modifié via EasyAdmin
        // Il faut utiliser une commande CLI ou un formulaire dédié
    }
}
