<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Carte;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CarteCrudController extends AbstractCrudController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Carte::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Carte')
            ->setEntityLabelInPlural('Cartes')
            ->setPageTitle('index', 'Mes Cartes')
            ->setPageTitle('edit', 'Éditer la Carte')
            ->setPageTitle('detail', 'Détails de la Carte')
            ->setDefaultSort(['validFrom' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/crud/carte_index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom de la carte')
            ->setRequired(true);

        yield AssociationField::new('restaurant', 'Restaurant')
            ->setRequired(true);

        yield DateField::new('validFrom', 'Valide du')
            ->setFormat('dd/MM/yyyy')
            ->setRequired(true);

        yield DateField::new('validTo', 'Valide au')
            ->setFormat('dd/MM/yyyy')
            ->setHelp('Laissez vide pour une validité illimitée')
            ->hideOnIndex();

        yield BooleanField::new('isPublished', 'Publiée')
            ->setHelp('La carte est-elle visible publiquement ?');

        yield TextField::new('slug', 'Identifiant')
            ->onlyOnDetail()
            ->setHelp('Identifiant unique de la carte');

        yield DateTimeField::new('createdAt', 'Créée le')
            ->onlyOnIndex()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updatedAt', 'Modifiée le')
            ->hideOnIndex()
            ->onlyOnDetail()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function configureActions(Actions $actions): Actions
    {


        // Custom action to edit via wizard
        $editAction = Action::new('editWizard', 'Éditer', 'fa fa-edit')
            ->linkToCrudAction('edit');

        // Custom action to create via wizard
        $createAction = Action::new('createWizard', 'Créer une Carte')
            ->linkToRoute('app_carte_wizard')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary action-new');

        return $actions
            ->add(Crud::PAGE_INDEX, $editAction)
            ->add(Crud::PAGE_INDEX, $createAction)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->addCssClass('btn btn-outline-danger')
                    ->displayAsButton()
                    ->setHtmlAttributes(['onclick' => 'return confirm("Êtes-vous sûr de vouloir supprimer cette carte ?")']);
            });
    }

    public function edit(AdminContext $context): RedirectResponse
    {
        $carte = $context->getEntity()->getInstance();

        if (!$carte instanceof Carte) {
            throw new \InvalidArgumentException('L\'entité doit être de type Carte');
        }

        // Rediriger vers le wizard avec l'ID de la carte, directement à l'étape 3
        return $this->redirectToRoute('app_carte_wizard', [
            'carteId' => $carte->getId(),
            'step' => 'step4'
        ]);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Multi-tenancy: only show cartes from user's restaurants
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();

            $qb->join('entity.restaurant', 'r')
                ->andWhere('r.owner = :user')
                ->setParameter('user', $user);
        }

        return $qb;
    }
}
