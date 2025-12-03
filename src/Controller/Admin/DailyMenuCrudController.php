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

class DailyMenuCrudController extends AbstractCrudController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

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
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
            yield FormField::addPanel('Détails du Menu du Jour')
                ->setIcon('fas fa-utensils')
                ->setRequired(true)
                ->setCssClass('panel-classy bg-rose-400 p-3 mb-4 mt-4');
            yield TextField::new('titre', 'Nom du menu')
                ->setRequired(true)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'samedi 22 Novembre');

            yield NumberField::new('price_epd', 'Tarif de votre formule Entrée + Plat + Dessert')
                ->setCssClass('placeholder-gastro')
                ->setRequired(true)
                ->setNumDecimals(2)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'ex: 15.50')
                ->setColumns(4);

            yield NumberField::new('price_ep', 'Tarif de votre formule Entrée + Plat')
                ->setRequired(true)
                ->setNumDecimals(2)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'ex: 12.50')
                ->setColumns(4);

            yield NumberField::new('price_pd', 'Tarif de votre formule Plat + Dessert')
                ->setRequired(true)
                ->setNumDecimals(2)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'ex: 10.50')
                ->setColumns(4);

            yield FormField::addPanel('Composition du Menu')
                ->setIcon('fas fa-list')
                ->setCssClass('panel-classy bg-accent-200 p-3 mb-4 mt-4');
                yield TextField::new('daily_entree', 'Entrée')
                ->setRequired(true)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'ex: Salade de chèvre chaud')
                ->hideOnIndex()
                ->setColumns(6);

                yield TextField::new('daily_plat', 'Plat')
                ->setRequired(true)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'ex: Filet de poulet à la crème et aux champignons')
                ->hideOnIndex()
                ->setColumns(6);

                yield TextField::new('daily_dessert', 'Dessert')
                ->setRequired(true)
                ->setCssClass('placeholder-gastro')
                ->setHtmlAttribute('placeholder', 'ex: Tarte aux pommes maison')
                ->hideOnIndex();

                yield BooleanField::new('status', 'Publié')
                    ->setHelp('Cochez pour rendre ce menu visible publiquement');
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
