<?php

namespace App\Controller\Admin;

use App\Entity\ForumTopic;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ForumTopicCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ForumTopic::class;
    }

    public function __construct(private Security $security) {}

    public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $entity->setAuthor($this->security->getUser());

        return $entity;
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title'),
            TextEditorField::new('description'),
            BooleanField::new('is_locked')->setLabel('Verrouillé')->hideOnForm(),
            BooleanField::new('is_pinned')->setLabel('Épinglé')->hideOnForm(),
            IntegerField::new('view_count')->setLabel('Nombre de vues')->hideOnForm(),
            BooleanField::new('deleted')->setLabel('Supprimé')->hideOnForm(),
            BooleanField::new('valid')->setLabel('Actif'),
            DateTimeField::new('created_at')->hideOnForm(),
            DateTimeField::new('updated_at')->hideOnForm(),
        ];
    }
}
