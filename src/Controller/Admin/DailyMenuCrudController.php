<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class DailyMenuCrudController extends AbstractCrudController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Ardoise::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Menu du Jour')
            ->setEntityLabelInPlural('Menus du Jour')
            ->setPageTitle('index', 'Menus du Jour')
            ->setPageTitle('new', 'Composer votre Menu du Jour')
            ->setPageTitle('edit', 'Modifier Menu du Jour')
            ->setDefaultSort(['id' => 'DESC'])
            ->setFormThemes([
                'admin/form/template_choice.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(6);
        yield FormField::addFieldset("Personnalisez l'affichage de votre menu")
            ->setCssClass('panel-classy bg-secondary-500 p-3 mb-4 mt-4')
            ;
        yield TextField::new('titre', 'Nom du menu')
            ->setRequired(true)
            ->setCssClass('placeholder-gastro mb-1')
            ->setHtmlAttribute('placeholder', 'samedi 22 Novembre');

        yield NumberField::new('price_pj', 'Plat du jour')
            ->setRequired(true)
            ->setNumDecimals(2)
            ->setCssClass('placeholder-gastro')
            ->setHtmlAttribute('placeholder', 'ex: 12.50')
            ->setColumns(6);

        yield NumberField::new('price_epd', 'Entrée + Plat + Dessert')
            ->setCssClass('placeholder-gastro')
            ->setRequired(true)
            ->setNumDecimals(2)
            ->setCssClass('placeholder-gastro')
            ->setHtmlAttribute('placeholder', 'ex: 15.50')
            ->setColumns(6);


        yield NumberField::new('price_pd', 'Plat + Dessert')
            ->setRequired(true)
            ->setNumDecimals(2)
            ->setCssClass('placeholder-gastro mb-5')
            ->setHtmlAttribute('placeholder', 'ex: 10.50')
            ->setColumns(6);

        // 🧱 Colonne 2 : contenu du jour
        yield FormField::addColumn(6);
        yield FormField::addFieldset("Que mange-t-on de bon aujourd'hui ?")
            ->setCssClass('panel-classy bg-success-200 p-3 mb-4 mt-4')
            ->setIcon('fas fa-concierge-bell');
        yield TextField::new('daily_entree', 'Entrée')
            ->setRequired(true)
            ->setCssClass('placeholder-gastro')
            ->setHtmlAttribute('placeholder', 'ex: Salade de chèvre chaud')
            ->hideOnIndex();

        yield TextField::new('daily_plat', 'Plat')
            ->setRequired(true)
            ->setCssClass('placeholder-gastro')
            ->setHtmlAttribute('placeholder', 'ex: Filet de poulet à la crème et aux champignons')
            ->hideOnIndex();

        yield TextField::new('daily_dessert', 'Dessert')
            ->setRequired(true)
            ->setCssClass('placeholder-gastro')
            ->setHtmlAttribute('placeholder', 'ex: Tarte aux pommes maison')
            ->hideOnIndex();

        yield BooleanField::new('status', 'Cochez pour rendre ce menu visible publiquement');

        yield FormField::addColumn(12);
        yield FormField::addFieldset("Personnalisez l'affichage de votre menu")
            ->setCssClass('panel-classy bg-sidebar-200 p-3 mb-4 mt-4e');
        yield ChoiceField::new('template', 'Choisissez votre template')
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
            ->renderAsNativeWidget(false);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Filtrer uniquement les menus de type DAILY
        $qb->andWhere('entity.type = :type')
            ->setParameter('type', Ardoise::TYPE_DAILY);

        // Multi-tenancy: ne montrer que les menus de l'utilisateur courant (sauf super admin)
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('entity.owner = :user')
                ->setParameter('user', $this->getUser());
        }

        return $qb;
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        /** @var Ardoise $entityInstance */
        if (!$entityInstance->getType()) {
            $entityInstance->setType(Ardoise::TYPE_DAILY);
        }

        if (!$entityInstance->getOwner()) {
            $entityInstance->setOwner($this->getUser());
        }

        parent::persistEntity($entityManager, $entityInstance);

        // Generer l'URL publique du menu
        $this->addPublicUrlFlash($entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
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
