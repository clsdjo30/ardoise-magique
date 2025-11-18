<?php

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Form\SectionType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArdoiseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ardoise::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ardoise')
            ->setEntityLabelInPlural('Ardoises')
            ->setPageTitle('index', 'Gestion des Ardoises')
            ->setPageTitle('new', 'Créer une nouvelle ardoise')
            ->setPageTitle('edit', 'Modifier l\'ardoise')
            ->setDefaultSort(['dateCreation' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // --- ONGLET 1 : CONFIGURATION ---
        yield FormField::addTab('1. Configuration')
            ->setIcon('fa fa-cog'); // Icône d'engrenage

        yield TextField::new('titre', 'Titre de l\'ardoise')
            ->setHelp('Ex: Menu du Jour, Carte d\'Automne, Menu St Valentin');

        yield DateField::new('dateCreation', 'Date de création')
            ->setFormat('dd/MM/yyyy');

        yield BooleanField::new('isActive', 'Mettre en ligne')
            ->renderAsSwitch(false);

        // --- ONGLET 2 : TARIFICATION ---
        yield FormField::addTab('2. Tarifs & Formules')
            ->setIcon('fa fa-euro-sign') // Icône Euro
            ->setHelp('Définissez ici les prix globaux. Laissez vide pour une carte sans menu.');

        yield MoneyField::new('prixComplet', 'Prix Menu Complet')
            ->setCurrency('EUR')
            ->setStoredAsCents(false)
            ->setHelp('Prix pour le menu complet (toutes les sections)');

        yield FormField::addRow(); // Force le passage à la ligne pour aligner les formules

        yield MoneyField::new('prixEntreePlat', 'Formule Entrée + Plat')
            ->setCurrency('EUR')
            ->setStoredAsCents(false)
            ->setHelp('Prix de la formule courte Entrée + Plat');

        yield MoneyField::new('prixPlatDessert', 'Formule Plat + Dessert')
            ->setCurrency('EUR')
            ->setStoredAsCents(false)
            ->setHelp('Prix de la formule courte Plat + Dessert');

        yield BooleanField::new('afficherPrixFormules', 'Afficher les prix des formules')
            ->setHelp('Cochez pour afficher les prix des formules sur l\'ardoise publique');

        // --- ONGLET 3 : COMPOSITION ---
        yield FormField::addTab('3. Composition de la Carte')
            ->setIcon('fa fa-utensils'); // Icône couverts

        yield CollectionField::new('sections')
            ->setEntryType(SectionType::class)
            ->setLabel('Sections du menu')
            ->setHelp('Ajoutez ici les différentes sections de votre menu (Mise en bouche, Entrées, Plats, Desserts...)')
            ->allowAdd(true)
            ->allowDelete(true)
            ->renderExpanded(true) // IMPORTANT : Affiche les sections ouvertes par défaut
            ->setEntryIsComplex(true); // Optimisation visuelle pour les sous-formulaires
    }
}
