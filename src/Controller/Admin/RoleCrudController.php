<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class RoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('roleName', 'Nom du rôle'),
            TextField::new('label', 'Label lisible'),
            ChoiceField::new('sections', 'Sections à gérer')
                ->setChoices([
                    'Articles' => 'article',
                    'Podcasts' => 'podcast',
                    'Archives' => 'archive',
                    'Enseignements' => 'enseignement',
                    'Utilisateurs' => 'user',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
        ];
    }
}