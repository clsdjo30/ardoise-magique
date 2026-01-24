<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OpeningHour;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\OpeningHourType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class RestaurantCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Custom action for opening hours
        $openingHoursAction = Action::new('openingHours', 'Horaires', 'fa fa-clock')
            ->linkToRoute('admin_opening_hours_edit', function (Restaurant $restaurant): array {
                return ['id' => $restaurant->getId()];
            })
            ->setCssClass('btn btn-outline-info')
            ->setHtmlAttributes(['title' => 'Gérer les horaires d\'ouverture']);

        return $actions
            ->add(Crud::PAGE_INDEX, $openingHoursAction)
            ->add(Crud::PAGE_DETAIL, $openingHoursAction)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Ajouter un Nouveau Restaurant')
                    ->setCssClass('btn btn-primary action-new')
                    ->setHtmlAttributes(['title' => 'Créer un nouveau restaurant']);
            });
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Restaurant')
            ->setEntityLabelInPlural('Restaurants')
            ->setPageTitle('index', 'Mes Restaurants')
            ->setPageTitle('new', 'Nouveau Restaurant')
            ->setPageTitle('edit', 'Modifier Restaurant')
            ->setDefaultSort(['id' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/crud/restaurant_index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(6);
        yield FormField::addFieldset('Informations du Restaurant')
            ->setCssClass('panel-classy bg-primary-200 p-3 mb-4 mt-4');

        yield TextField::new('name', 'Nom du restaurant')
            ->setRequired(true)
            ->setHelp('Le nom de votre établissement');

        yield TextField::new('address', 'Adresse')
            ->setRequired(true)
            ->setHelp('Numéro et nom de rue');

        yield FormField::addColumn(6);
        yield FormField::addFieldset('Localisation')
            ->setCssClass('panel-classy bg-secondary-200 p-3 mb-4 mt-4');

        yield TextField::new('zipCode', 'Code postal')
            ->setRequired(true)
            ->setColumns(6);

        yield TextField::new('city', 'Ville')
            ->setRequired(true)
            ->setColumns(6);

        yield TextField::new('departement', 'Département')
            ->setHelp('Ex: Haute-Garonne, Paris, etc.')
            ->setColumns(12);

        yield FormField::addColumn(12);
        yield FormField::addFieldset('Contact et Réseaux Sociaux')
            ->setCssClass('panel-classy bg-success-200 p-3 mb-4 mt-4');

        yield TextField::new('phoneNumber', 'Téléphone')
            ->setHelp('Format: 01 23 45 67 89')
            ->setColumns(6);

        yield TextField::new('facebookPage', 'Page Facebook')
            ->setHelp('URL de votre page Facebook')
            ->setColumns(6);

        // Opening hours - only on edit page
        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_DETAIL) {
            yield FormField::addColumn(12);
            yield FormField::addFieldset('Horaires d\'ouverture')
                ->setCssClass('panel-classy bg-info-200 p-3 mb-4 mt-4')
                ->setHelp('Gérez les horaires d\'ouverture de votre restaurant');

            yield CollectionField::new('openingHours', 'Horaires')
                ->setEntryType(OpeningHourType::class)
                ->setEntryIsComplex(true)
                ->allowAdd()
                ->allowDelete()
                ->setColumns(12)
                ->setHelp('Ajoutez les créneaux d\'ouverture (midi et soir pour chaque jour)');
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Multi-tenancy: ne montrer que les restaurants de l'utilisateur courant (sauf super admin)
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();

            $qb->andWhere('entity.owner = :user')
                ->setParameter('user', $user);
        }

        return $qb;
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        /** @var Restaurant $entityInstance */
        /** @var User $user */
        $user = $this->getUser();

        if (!$entityInstance->getOwner()) {
            $entityInstance->setOwner($user);
        }

        parent::persistEntity($entityManager, $entityInstance);

        $this->addFlash('success', sprintf(
            'Restaurant "%s" créé avec succès !',
            $entityInstance->getName()
        ));
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);

        $this->addFlash('success', sprintf(
            'Restaurant "%s" mis à jour avec succès !',
            $entityInstance->getName()
        ));
    }
}
