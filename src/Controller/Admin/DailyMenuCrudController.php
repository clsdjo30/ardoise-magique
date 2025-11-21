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
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            ->setPageTitle('new', 'Nouveau Menu du Jour')
            ->setPageTitle('edit', 'Edition Menu du Jour')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('titre', 'Titre du menu')
            ->setHelp('Ex: Menu du Jour du 21 Novembre');

        yield BooleanField::new('status', 'Publie')
            ->setHelp('Cochez pour rendre ce menu visible publiquement');

        yield TextareaField::new('daily_entree', 'Entree')
            ->setHelp('Decrivez l\'entree du jour')
            ->hideOnIndex();

        yield TextareaField::new('daily_plat', 'Plat')
            ->setHelp('Decrivez le plat du jour')
            ->hideOnIndex();

        yield TextareaField::new('daily_dessert', 'Dessert')
            ->setHelp('Decrivez le dessert du jour')
            ->hideOnIndex();

        yield MoneyField::new('price_epd', 'Prix E+P+D')
            ->setCurrency('EUR')
            ->setHelp('Prix de la formule Entree + Plat + Dessert');

        yield MoneyField::new('price_ep', 'Prix E+P')
            ->setCurrency('EUR')
            ->setHelp('Prix de la formule Entree + Plat');

        yield MoneyField::new('price_pd', 'Prix P+D')
            ->setCurrency('EUR')
            ->setHelp('Prix de la formule Plat + Dessert');
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
