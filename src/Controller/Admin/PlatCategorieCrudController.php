<?php

namespace App\Controller\Admin;

use App\Entity\PlatCategorie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PlatCategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlatCategorie::class;
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
