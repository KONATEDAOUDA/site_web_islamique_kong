<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    public function configureFields(string $pageName): iterable
    {
        // Récupérer dynamiquement tous les rôles de la table
        $roleRepo = $this->em->getRepository(Role::class);
        $roleEntities = $roleRepo->findAll();
        $roles = [];
        foreach ($roleEntities as $role) {
            $roles[$role->getLabel()] = $role->getRoleName();
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            EmailField::new('email', 'Email'),
            TextField::new('password', 'Mot de passe')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmer le mot de passe'],
                    'mapped' => false,
                ])
                ->onlyWhenCreating(),
            ImageField::new('profilePicture', 'Photo de profil')
                ->setBasePath('assets/uploads/users')
                ->setUploadDir('public/assets/uploads/users')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices($roles)
                ->allowMultipleChoices()
                ->renderExpanded(),
            BooleanField::new('isVerified', 'Vérifié'),
            TextField::new('phone', 'Téléphone'),
            TextEditorField::new('bio', 'Biographie'),
            TextField::new('location', 'Localisation'),
            DateTimeField::new('lastLoginAt', 'Dernière connexion')->hideOnForm(),
            DateTimeField::new('lastActivityAt', 'Dernière activité')->hideOnForm(),
            TextField::new('lastLoginIp', 'IP de connexion')->hideOnForm(),
            TextField::new('lastLoginUserAgent', 'User-Agent de connexion')->hideOnForm(),
            IntegerField::new('loginCount', 'Nombre de connexions')->hideOnForm(),
            DateTimeField::new('passwordChangedAt', 'Changement de mot de passe')->hideOnForm(),
            BooleanField::new('mustChangePassword', 'Doit changer de mot de passe'),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnForm(),

        ];
    }
}
