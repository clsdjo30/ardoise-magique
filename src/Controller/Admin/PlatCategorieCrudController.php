<?php

namespace App\Controller\Admin;

use App\Entity\PlatCategorie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PlatCategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlatCategorie::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->hideOnForm()->hideOnDetail(),
            TextField::new('titre'),
        ];
    }

}
