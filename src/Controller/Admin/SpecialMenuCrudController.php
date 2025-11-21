<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Entity\User;
use App\Form\ArdoiseItemType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SpecialMenuCrudController extends AbstractCrudController
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
            ->setEntityLabelInSingular('Menu Special')
            ->setEntityLabelInPlural('Menus Speciaux')
            ->setPageTitle('index', 'Menus Speciaux')
            ->setPageTitle('new', 'Nouveau Menu Special')
            ->setPageTitle('edit', 'Edition Menu Special')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('titre', 'Titre du menu')
            ->setHelp('Ex: Menu de Noel 2024, Menu Saint-Valentin');

        yield BooleanField::new('status', 'Publie')
            ->setHelp('Cochez pour rendre ce menu visible publiquement');

        yield MoneyField::new('special_global_price', 'Prix global')
            ->setCurrency('EUR')
            ->setHelp('Prix total du menu special (optionnel)')
            ->hideOnIndex();

        yield CollectionField::new('items', 'Composition du menu')
            ->setEntryType(ArdoiseItemType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
            ])
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex(true)
            ->hideOnIndex()
            ->setHelp('Ajoutez les differents elements de votre menu special');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Filtrer uniquement les menus de type SPECIAL
        $qb->andWhere('entity.type = :type')
            ->setParameter('type', Ardoise::TYPE_SPECIAL);

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
            $entityInstance->setType(Ardoise::TYPE_SPECIAL);
        }

        if (!$entityInstance->getOwner()) {
            $entityInstance->setOwner($this->getUser());
        }

        // Mise a jour automatique de la position des items
        $position = 0;
        foreach ($entityInstance->getItems() as $item) {
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
        foreach ($entityInstance->getItems() as $item) {
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

        $publicUrl = $this->urlGenerator->generate('app_public_menu', [
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
