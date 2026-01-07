<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PlatCategorie;
use App\Repository\SectionItemRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class PlatCategorieCrudController extends AbstractCrudController
{
    public function __construct(
        private SectionItemRepository $sectionItemRepository,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return PlatCategorie::class;
    }

    public function delete(AdminContext $context): Response
    {
        /** @var PlatCategorie $categorie */
        $categorie = $context->getEntity()->getInstance();

        // Check if this category has plats that are used in cartes
        $platsInUse = [];

        foreach ($categorie->getPlatCatalogues() as $plat) {
            foreach ($plat->getVariants() as $variant) {
                $sectionItems = $this->sectionItemRepository->findBy(['platVariant' => $variant]);
                if (count($sectionItems) > 0) {
                    if (!in_array($plat->getName(), $platsInUse)) {
                        $platsInUse[] = $plat->getName();
                    }
                }
            }
        }

        if (count($platsInUse) > 0) {
            $message = sprintf(
                'Impossible de supprimer la catégorie "%s" car elle contient %d plat(s) utilisé(s) dans des cartes : %s. Retirez d\'abord ces plats des cartes.',
                $categorie->getTitre(),
                count($platsInUse),
                implode(', ', array_slice($platsInUse, 0, 3)) . (count($platsInUse) > 3 ? '...' : '')
            );
            $this->addFlash('danger', $message);

            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        // Also check if category has any plats (optional - you might want to allow deleting empty categories)
        if ($categorie->getPlatCatalogues()->count() > 0) {
            $message = sprintf(
                'Impossible de supprimer la catégorie "%s" car elle contient %d plat(s). Supprimez ou déplacez d\'abord ces plats.',
                $categorie->getTitre(),
                $categorie->getPlatCatalogues()->count()
            );
            $this->addFlash('warning', $message);

            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }

     public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Categorie des Plats de la carte')
            ->setEntityLabelInPlural('Catégories des Plats de la carte')
            ->setPageTitle('index', 'Catégories des Plats de la carte')
            ->setPageTitle('new', 'Créer une catégorie de plat')
            ->setPageTitle('edit', 'Modifier une catégorie de plat')
            ->setDefaultSort(['id' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/crud/categorie_index.html.twig')
            ->setFormThemes([
                'admin/form/template_choice.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

     public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-fire')
                    ->addCssClass('btn btn-outline-danger')
                    ->displayAsButton()
                    ->setHtmlAttributes(['onclick' => 'return confirm("Êtes-vous sûr de vouloir supprimer cette catégorie ?")']);
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setLabel('Ajouter une Nouvelle Catégorie de Plat')
                    ->setCssClass('btn btn-primary action-new')
                    ->setHtmlAttributes(['title' => 'Créer une nouvelle catégorie de plat']);
            });
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->hideOnForm()->hideOnDetail(),
            TextField::new('titre'),
        ];
    }

}
