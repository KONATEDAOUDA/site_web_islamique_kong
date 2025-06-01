<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Podcast;
use App\Entity\Archive;
use App\Entity\Enseignement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DevDataLoader
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function loadTestData(): void
    {
        $this->loadUsers();
        $this->loadArticles();
        $this->loadPodcasts();
        $this->loadArchives();
        $this->loadEnseignements();
        
        $this->entityManager->flush();
    }

    private function loadUsers(): void
    {
        // Créer un utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@islamique-kong.ci');
        $admin->setFirstName('Admin');
        $admin->setLastName('Kong');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'admin123'));
        $admin->setIsVerified(true);
        
        $this->entityManager->persist($admin);

        // Créer un utilisateur blog manager
        $blogManager = new User();
        $blogManager->setEmail('blog@islamique-kong.ci');
        $blogManager->setFirstName('Blog');
        $blogManager->setLastName('Manager');
        $blogManager->setRoles(['ROLE_BLOG_MANAGER']);
        $blogManager->setPassword($this->userPasswordHasher->hashPassword($blogManager, 'blog123'));
        $blogManager->setIsVerified(true);
        
        $this->entityManager->persist($blogManager);

        // Créer un utilisateur standard
        $user = new User();
        $user->setEmail('user@islamique-kong.ci');
        $user->setFirstName('Utilisateur');
        $user->setLastName('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'user123'));
        $user->setIsVerified(true);
        
        $this->entityManager->persist($user);
    }

    private function loadArticles(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@islamique-kong.ci']);
        
        for ($i = 1; $i <= 5; $i++) {
            $article = new Article();
            $article->setTitle("Article de test #{$i}");
            $article->setContent("Contenu de l'article de test numéro {$i}. Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
            $article->setSlug("article-test-{$i}");
            $article->setAuthor($admin);
            $article->setPublishedAt(new \DateTime("-{$i} days"));
            $article->setCreatedAt(new \DateTime("-{$i} days"));
            $article->setUpdatedAt(new \DateTime("-{$i} days"));
            
            $this->entityManager->persist($article);
        }
    }

    private function loadPodcasts(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@islamique-kong.ci']);
        
        for ($i = 1; $i <= 3; $i++) {
            $podcast = new Podcast();
            $podcast->setTitle("Podcast #{$i}");
            $podcast->setDescription("Description du podcast numéro {$i}");
            $podcast->setSlug("podcast-{$i}");
            $podcast->setAuthor($admin);
            $podcast->setDuration("00:30:00");
            $podcast->setCreatedAt(new \DateTime("-{$i} weeks"));
            $podcast->setUpdatedAt(new \DateTime("-{$i} weeks"));
            
            $this->entityManager->persist($podcast);
        }
    }

    private function loadArchives(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $archive = new Archive();
            $archive->setTitle("Archive historique #{$i}");
            $archive->setDescription("Description de l'archive numéro {$i}");
            $archive->setCreatedAt(new \DateTime("-{$i} months"));
            $archive->setUpdatedAt(new \DateTime("-{$i} months"));
            
            $this->entityManager->persist($archive);
        }
    }

    private function loadEnseignements(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@islamique-kong.ci']);
        
        for ($i = 1; $i <= 3; $i++) {
            $enseignement = new Enseignement();
            $enseignement->setTitle("Enseignement #{$i}");
            $enseignement->setContent("Contenu de l'enseignement numéro {$i}");
            $enseignement->setSlug("enseignement-{$i}");
            $enseignement->setAuthor($admin);
            $enseignement->setCreatedAt(new \DateTime("-{$i} weeks"));
            $enseignement->setUpdatedAt(new \DateTime("-{$i} weeks"));
            
            $this->entityManager->persist($enseignement);
        }
    }

    public function clearTestData(): void
    {
        // Supprimer toutes les données de test
        $this->entityManager->createQuery('DELETE FROM App\Entity\Article')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Podcast')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Archive')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Enseignement')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    public function isTestDataLoaded(): bool
    {
        $userCount = $this->entityManager->getRepository(User::class)->count([]);
        return $userCount > 0;
    }
}
