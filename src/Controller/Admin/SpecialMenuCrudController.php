<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\ArdoiseItemType;
use App\Form\EntreeType;
use App\Form\PlatType;
use App\Form\DessertType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\RestaurantRepository;

class SpecialMenuCrudController extends AbstractCrudController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
        private RestaurantRepository $restaurantRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Ardoise::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Menu Spécial')
            ->setEntityLabelInPlural('Menus Spéciaux')
            ->setPageTitle('index', 'Menus Spéciaux')
            ->setPageTitle('new', 'Nouveau Menu Spécial')
            ->setPageTitle('edit', 'Edition Menu Spécial')
            ->setDefaultSort(['id' => 'DESC'])
            ->setFormThemes([
                'admin/form/template_choice.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            //MENU SPECIAL
            FormField::addTab('Généralités du Menu Spécial'),
            FormField::addPanel('Tarifs et Informations Générales')->addCssClass('panel-classy bg-secondary-500 p-3 mb-4 mt-4'),

            TextField::new('titre', 'Titre du menu')
                ->setHelp('Ex: Menu de Noël 2024, Menu Saint-Valentin')
                ->setColumns(6),
            NumberField::new('special_global_price', 'Prix global')
                ->setNumDecimals(2)
                ->setHelp('Prix total du menu spécial (ex: 45.00) - Optionnel')
                ->hideOnIndex()
                ->setColumns(6),

            BooleanField::new('status', 'Publié')
                ->setHelp('Cochez pour rendre ce menu visible publiquement'),


            FormField::addFieldset("Personnalisez l'affichage de votre menu")
                ->setCssClass('panel-classy bg-sidebar-200 p-3 mb-4 mt-4e'),
            ChoiceField::new('template', 'Choisissez votre template')
                ->setChoices([
                    'Bistrot' => Ardoise::TEMPLATE_BISTROT,
                    'Tradition' => Ardoise::TEMPLATE_TRADITIONNEL,
                    'Brut' => Ardoise::TEMPLATE_BRUT,
                    'Classe' => Ardoise::TEMPLATE_CLASSE,
                    'Digital' => Ardoise::TEMPLATE_DIGITAL,
                    'Magazine' => Ardoise::TEMPLATE_MAGAZINE,
                    'Marché' => Ardoise::TEMPLATE_MARCHE,
                    'Raffiné' => Ardoise::TEMPLATE_RAFINE,
                ])
                ->setColumns(12)
                ->setRequired(true)
                ->setFormTypeOption('expanded', true)
                ->setFormTypeOption('attr', ['class' => 'template-grid'])
                ->setFormTypeOption('choice_attr', function ($choice, $key, $value) {
                    return [
                        'class' => 'template-radio-option',
                        'data-template-value' => $value,
                        'data-template-label' => $key,
                        'data-template-image' => '/images/vignette_jour/' . $value . '.webp'
                    ];
                })
                ->setFormTypeOption('row_attr', ['class' => 'template-field-row'])
                ->renderAsNativeWidget(false),

            // Deuxieme Tab - MLes Entrées
            FormField::addTab('Vos Entrées'),
            FormField::addPanel('Ajouter ici vos entrées au menu spécial')->addCssClass('panel-classy bg-success-200 p-3 mb-4 mt-4'),
            CollectionField::new('entree', 'Composition du menu')
                ->setColumns(12)
                ->setEntryIsComplex(true)
                ->showEntryLabel(true)
                ->setEntryType(EntreeType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->allowAdd(true)
                ->allowDelete(true)
                ->hideOnIndex()
                ->setHelp('Ajoutez les différents éléments de votre menu spécial')
                ->setCssClass('collection-custom collection-entree'),

            // Troisieme Tab - Les Plats
            FormField::addTab('Vos Plats'),
            FormField::addPanel('Ajouter ici vos plats au menu spécial')->addCssClass('panel-classy bg-danger-200 p-3 mb-4 mt-4'),
            CollectionField::new('plat', 'Plats de la composition du menu')
                ->setColumns(12)
                ->setEntryType(PlatType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->allowAdd(true)
                ->allowDelete(true)
                ->setEntryIsComplex(true)
                ->hideOnIndex()
                ->setHelp('Ajoutez les différents éléments de votre menu spécial')
                ->setCssClass('collection-custom collection-plat'),

            // Quatrieme Tab - Les Desserts
            FormField::addTab('Vos Dessert'),
            FormField::addPanel('Ajouter ici vos desserts au menu spécial')->addCssClass('panel-classy bg-rose-400 p-3 mb-4 mt-4'),
            CollectionField::new('dessert', 'Desserts de la composition du menu')
                ->setColumns(12)
                ->setEntryType(DessertType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->allowAdd(true)
                ->allowDelete(true)
                ->hideOnIndex()
                ->setHelp('Ajoutez les différents éléments de votre menu spécial')
                ->setCssClass('collection-custom collection-dessert'),

            // Cinquieme Tab - Les supplements
            FormField::addTab('Supplement'),
            FormField::addPanel('Ajouter ici les supplements au menu spécial')->addCssClass('panel-classy bg-accent-300 p-3 mb-4 mt-4'),
            CollectionField::new('item', 'Suppléments de la composition du menu')
                ->setColumns(12)
                ->setEntryType(ArdoiseItemType::class)
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->allowAdd(true)
                ->allowDelete(true)
                ->hideOnIndex()
                ->setHelp('Ajoutez les différents éléments de votre menu spécial')
                ->setCssClass('collection-custom collection-supplement'),

        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Filtrer uniquement les menus de type SPECIAL
        $qb->andWhere('entity.type = :type')
            ->setParameter('type', Ardoise::TYPE_SPECIAL);

        // Multi-tenancy: ne montrer que les menus des restaurants de l'utilisateur courant (sauf super admin)
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();

            $qb->join('entity.restaurant', 'r')
                ->andWhere('r.owner = :user')
                ->setParameter('user', $user);
        }

        return $qb;
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        /** @var Ardoise $entityInstance */
        /** @var User $user */
        $user = $this->getUser();

        if (!$entityInstance->getType()) {
            $entityInstance->setType(Ardoise::TYPE_SPECIAL);
        }

        // Assigner le restaurant sélectionné en session ou le premier restaurant de l'utilisateur
        if (!$entityInstance->getRestaurant()) {
            $session = $this->requestStack->getSession();
            $selectedRestaurantId = $session->get('selected_restaurant_id');

            if ($selectedRestaurantId) {
                $restaurant = $this->restaurantRepository->find($selectedRestaurantId);
                // Vérifier que le restaurant appartient à l'utilisateur
                if ($restaurant && $restaurant->getOwner() === $user) {
                    $entityInstance->setRestaurant($restaurant);
                    // Clear session after use
                    $session->remove('selected_restaurant_id');
                } else {
                    $restaurant = $user->getFirstRestaurant();
                }
            } else {
                $restaurant = $user->getFirstRestaurant();
            }

            if (!$restaurant) {
                throw new \RuntimeException('Vous devez créer un restaurant avant de créer un menu');
            }

            if (!$entityInstance->getRestaurant()) {
                $entityInstance->setRestaurant($restaurant);
            }
        }

        // Mise a jour automatique de la position des items
        $position = 0;
        foreach ($entityInstance->getEntree() as $entree) {
            $entree->setPosition($position++);
        }
        $position = 0;
        foreach ($entityInstance->getPlat() as $plat) {
            $plat->setPosition($position++);
        }
        $position = 0;
        foreach ($entityInstance->getDessert() as $dessert) {
            $dessert->setPosition($position++);
        }
        $position = 0;
        foreach ($entityInstance->getItem() as $item) {
            $item->setPosition($position++);
        }

        parent::persistEntity($entityManager, $entityInstance);

        // Generer l'URL publique du menu
        $this->addPublicUrlFlash($entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        /** @var Ardoise $entityInstance */
        // Mise a jour automatique de la position des items
        $position = 0;
        foreach ($entityInstance->getEntree() as $entree) {
            $entree->setPosition($position++);
        }
        $position = 0;
        foreach ($entityInstance->getPlat() as $plat) {
            $plat->setPosition($position++);
        }
        $position = 0;
        foreach ($entityInstance->getDessert() as $dessert) {
            $dessert->setPosition($position++);
        }
        $position = 0;
        foreach ($entityInstance->getItem() as $item) {
            $item->setPosition($position++);
        }

        parent::updateEntity($entityManager, $entityInstance);

        // Generer l'URL publique du menu
        $this->addPublicUrlFlash($entityInstance);
    }

    private function addPublicUrlFlash(Ardoise $ardoise): void
    {
        /** @var User $user */
        $user = $this->getUser();

        $publicUrl = $this->urlGenerator->generate('app_show_menu', [
            'restaurant' => $user->getSlug(),
            'slug' => $ardoise->getSlug()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addFlash('success', sprintf(
            'Menu cree avec succes ! URL publique : <a href="%s" target="_blank">%s</a>',
            $publicUrl,
            $publicUrl
        ));
    }
}
