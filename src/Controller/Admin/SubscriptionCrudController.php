<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Subscription;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubscriptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subscription::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Abonnement')
            ->setEntityLabelInPlural('Abonnements')
            ->setPageTitle('index', 'Gestion des Abonnements')
            ->setPageTitle('detail', fn (Subscription $subscription) => sprintf(
                'Abonnement #%d - %s',
                $subscription->getId(),
                $subscription->getUser()?->getEmail() ?? 'N/A'
            ))
            ->setPageTitle('edit', fn (Subscription $subscription) => sprintf(
                'Modifier l\'abonnement de %s',
                $subscription->getUser()?->getEmail() ?? 'N/A'
            ))
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['user.email', 'user.firstname', 'user.lastname', 'planCode', 'status'])
            ->setPaginatorPageSize(30);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm();

        yield AssociationField::new('user', 'Utilisateur')
            ->setRequired(true)
            ->formatValue(function ($value, Subscription $subscription) {
                $user = $subscription->getUser();
                if (!$user) {
                    return 'N/A';
                }
                return sprintf(
                    '%s %s (%s)',
                    $user->getFirstname() ?? '',
                    $user->getLastname() ?? '',
                    $user->getEmail()
                );
            });

        yield ChoiceField::new('planCode', 'Plan')
            ->setChoices([
                'Gratuit' => 'FREE',
                'Starter' => 'STARTER',
                'Premium' => 'PREMIUM',
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'FREE' => 'secondary',
                'STARTER' => 'info',
                'PREMIUM' => 'success',
            ]);

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Actif' => 'active',
                'En période d\'essai' => 'trialing',
                'Paiement en retard' => 'past_due',
                'Annulé' => 'canceled',
                'Incomplet' => 'incomplete',
            ])
            ->setRequired(true)
            ->renderAsBadges([
                'active' => 'success',
                'trialing' => 'info',
                'past_due' => 'warning',
                'canceled' => 'danger',
                'incomplete' => 'secondary',
            ]);

        yield DateTimeField::new('startedAt', 'Début')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnIndex();

        yield DateTimeField::new('expiresAt', 'Expiration')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnIndex();

        yield DateTimeField::new('canceledAt', 'Annulé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnIndex()
            ->hideOnForm();

        yield TextField::new('stripeSubscriptionId', 'Stripe Subscription ID')
            ->hideOnIndex();

        yield TextField::new('stripeCustomerId', 'Stripe Customer ID')
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', 'Créé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Mis à jour le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm()
            ->hideOnIndex();
    }
}
