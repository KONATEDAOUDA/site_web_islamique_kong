<?php

namespace App\Controller\Admin;

use App\Entity\MaitreIslamique;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use Symfony\Bundle\SecurityBundle\Security;

class MaitreIslamiqueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaitreIslamique::class;
    }

    public function __construct(private Security $security) {}
     public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $entity->setAuthor($this->security->getUser());
        $entity->setDeleted('0');

        return $entity;
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
            TextField::new('prenom'),
            ImageField::new('photo')
                ->setUploadDir('public/assets/uploads/maitres')
                ->setBasePath('assets/uploads/maitres'),
            TextEditorField::new('biographie'),
            TextEditorField::new('enseignements')->onlyOnForms(),
            TextField::new('specialite'),
            IntegerField::new('anneeNaissance', 'Année de naissance'),
            IntegerField::new('anneeDeces', 'Année de décès'),
            SlugField::new('slug')->setTargetFieldName(['nom', 'prenom'])->onlyOnForms(),
            BooleanField::new('isAlive', 'Toujours en vie'),
            BooleanField::new('deleted')->hideOnForm(),
            BooleanField::new('valid')->setLabel('Validé'),
            BooleanField::new('isPublished', 'Publié'),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
        ];
    }
}
