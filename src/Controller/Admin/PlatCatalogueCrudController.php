<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PlatCatalogue;
use App\Entity\User;
use App\Form\PlatVariantType;
use App\Repository\SectionItemRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class PlatCatalogueCrudController extends AbstractCrudController
{
    public function __construct(
        private SectionItemRepository $sectionItemRepository,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return PlatCatalogue::class;
    }

    public function delete(AdminContext $context): Response
    {
        /** @var PlatCatalogue $plat */
        $plat = $context->getEntity()->getInstance();

        // Check if any variant of this plat is used in a SectionItem
        $usedInSections = false;
        $cartesNames = [];

        foreach ($plat->getVariants() as $variant) {
            $sectionItems = $this->sectionItemRepository->findBy(['platVariant' => $variant]);
            if (count($sectionItems) > 0) {
                $usedInSections = true;
                foreach ($sectionItems as $item) {
                    $carteName = $item->getCarteSection()?->getCarte()?->getName();
                    if ($carteName && !in_array($carteName, $cartesNames)) {
                        $cartesNames[] = $carteName;
                    }
                }
            }
        }

        if ($usedInSections) {
            $message = sprintf(
                'Impossible de supprimer le plat "%s" car il est utilisé dans %s carte(s) : %s. Retirez-le d\'abord de ces cartes.',
                $plat->getName(),
                count($cartesNames),
                implode(', ', $cartesNames)
            );
            $this->addFlash('danger', $message);

            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-fire')
                    ->addCssClass('btn btn-outline-danger')
                    ->displayAsButton()
                    ->setHtmlAttributes(['onclick' => 'return confirm("Êtes-vous sûr de vouloir supprimer ce plat ?")']);
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Ajouter un Nouveau Plat')
                    ->setCssClass('btn btn-primary action-new')
                    ->setHtmlAttributes(['title' => 'Créer un nouveau Plat']);
            });
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Plat')
            ->setEntityLabelInPlural('Catalogue de Plats')
            ->setPageTitle('index', 'Ma Bibliothèque de Plats')
            ->setPageTitle('new', 'Nouveau Plat')
            ->setPageTitle('edit', 'Édition Plat')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->overrideTemplate('crud/index', 'admin/crud/plat_index.html.twig')
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        // Tab 1: Informations générales
        yield FormField::addTab('Informations du Plat');
        yield FormField::addPanel('Détails du plat')->addCssClass('panel-classy bg-secondary-500 p-3 mb-4 mt-4');

        yield TextField::new('name', 'Nom du plat')
            ->setHelp('Ex: Foie gras mi-cuit, Carpaccio de Saint-Jacques')
            ->setColumns(12);

        yield TextareaField::new('description', 'Description')
            ->setHelp('Décrivez le plat, ses ingrédients, sa préparation')
            ->setColumns(12)
            ->hideOnIndex();

        yield AssociationField::new('category', 'Catégorie')
            ->setHelp('Entrée, Plat, Dessert, Boisson, etc.')
            ->setColumns(6);

        yield AssociationField::new('tags', 'Tags')
            ->setHelp('Végétarien, Sans gluten, Fait maison, etc.')
            ->setColumns(6)
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('dd/MM/yyyy HH:mm');

        // Tab 2: Variantes (prix)
        yield FormField::addTab('Variantes & Prix');
        yield FormField::addPanel('Gérez les différentes variantes et prix')->addCssClass('panel-classy bg-secondary-500 p-3 mb-4 mt-4');

        yield CollectionField::new('variants', 'Variantes de prix')
            ->setEntryType(PlatVariantType::class)
            ->setEntryIsComplex(true)
            ->setHelp('Ajoutez différentes variantes (prix, format, options)')
            ->allowAdd()
            ->allowDelete()
            ->setColumns(12)
            ->hideOnIndex();
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Multi-tenancy: only show plats from current user
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
        /** @var PlatCatalogue $entityInstance */
        if (!$entityInstance->getOwner()) {
            /** @var User $user */
            $user = $this->getUser();
            $entityInstance->setOwner($user);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
