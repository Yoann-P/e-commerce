<?php

namespace App\Controller\Admin;

use App\Entity\Collections;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CollectionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Collections::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            TextField::new('description'),
            BooleanField::new('isMega'),
            TextField::new('button_text')->hideOnIndex(),
            UrlField::new('button_link')->hideOnIndex(),
            ImageField::new('imageUrl')
            ->setBasePath('assets/images/collections')
            ->setUploadDir('public/assets/images/collections')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired($pageName === Crud::PAGE_NEW),
        ];
    }
}
