<?php

namespace App\Controller\Admin;

use App\Entity\Params;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Validator\Constraints\Date;

class ParamsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Params::class;
    }

     public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $entity->setDeleted('0');

        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('real_id', 'Identifiant'),
            TextEditorField::new('use_terms', 'Parmètres d\'utilisation'),
            BooleanField::new('valid', 'Valide'),
            //BooleanField::new('deleted', 'Supprimé'),
            DateTimeField::new('created_at', 'Créé le')->hideOnForm(),
            DateTimeField::new('updated_at', 'Mis  jour le')->hideOnForm(),
        ];
    }
}
