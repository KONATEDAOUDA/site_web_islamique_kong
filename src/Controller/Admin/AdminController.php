<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\ArticleRepository;
use App\Repository\PodcastRepository;
use App\Repository\ArchiveRepository;
use App\Repository\UserRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\CommentRepository;
use App\Repository\EnseignementRepository;
use App\Repository\MaitreIslamiqueRepository;

class AdminController extends AbstractController
{
    private $security;
    private $articleRepository;
    private $podcastRepository;
    private $archiveRepository;
    private $userRepository;
    private $forumTopicRepository;
    private $commentRepository;
    private $enseignementRepository;
    private $maitreRepository;

    public function __construct(
        Security $security,
        ArticleRepository $articleRepository,
        PodcastRepository $podcastRepository,
        ArchiveRepository $archiveRepository,
        UserRepository $userRepository,
        ForumTopicRepository $forumTopicRepository,
        CommentRepository $commentRepository,
        EnseignementRepository $enseignementRepository,
        MaitreIslamiqueRepository $maitreRepository
    ) {
        $this->security = $security;
        $this->articleRepository = $articleRepository;
        $this->podcastRepository = $podcastRepository;
        $this->archiveRepository = $archiveRepository;
        $this->userRepository = $userRepository;
        $this->forumTopicRepository = $forumTopicRepository;
        $this->commentRepository = $commentRepository;
        $this->enseignementRepository = $enseignementRepository;
        $this->maitreRepository = $maitreRepository;
    }

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupérer les rôles de l'utilisateur pour adapter l'affichage
        $userRoles = $this->getUser()->getRoles();
        $currentUser = $this->getUser();

        // Initialiser les statistiques de base
        $stats = [
            'articles' => 0,
            'podcasts' => 0,
            'archives' => 0,
            'enseignements' => 0,
            'users' => 0,
            'topics' => 0,
            'comments' => 0,
        ];

        // Initialiser les derniers contenus
        $latestArticles = [];
        $latestPodcasts = [];
        $latestArchives = [];
        $latestEnseignements = [];
        $latestUsers = [];
        $userPermissions = $this->getUserPermissions($userRoles);

        // ROLE_ADMIN : Accès complet à toutes les statistiques
        if ($this->isGranted('ROLE_ADMIN')) {
            $stats = [
                'articles' => $this->articleRepository->count([]),
                'podcasts' => $this->podcastRepository->count([]),
                'archives' => $this->archiveRepository->count([]),
                'enseignements' => $this->enseignementRepository->count([]),
                'users' => $this->userRepository->count([]),
                'topics' => $this->forumTopicRepository->count([]),
                'comments' => $this->commentRepository->count([]),
            ];

            $latestArticles = $this->articleRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestPodcasts = $this->podcastRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestArchives = $this->archiveRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestEnseignements = $this->enseignementRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestUsers = $this->userRepository->findBy([], ['createdAt' => 'DESC'], 5);
        }
        // ROLE_SUPERVISOR : Vue d'ensemble mais pas de gestion utilisateurs
        elseif ($this->isGranted('ROLE_SUPERVISOR')) {
            $stats['articles'] = $this->articleRepository->count([]);
            $stats['podcasts'] = $this->podcastRepository->count([]);
            $stats['archives'] = $this->archiveRepository->count([]);
            $stats['enseignements'] = $this->enseignementRepository->count([]);
            $stats['topics'] = $this->forumTopicRepository->count([]);
            $stats['comments'] = $this->commentRepository->count([]);

            $latestArticles = $this->articleRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestPodcasts = $this->podcastRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestArchives = $this->archiveRepository->findBy([], ['createdAt' => 'DESC'], 5);
            $latestEnseignements = $this->enseignementRepository->findBy([], ['createdAt' => 'DESC'], 5);
        }
        // ROLE_BLOG_MANAGER : Uniquement les articles
        elseif ($this->isGranted('ROLE_BLOG_MANAGER')) {
            $stats['articles'] = $this->articleRepository->count([]);
            $stats['comments'] = $this->commentRepository->count([]);
            $latestArticles = $this->articleRepository->findBy([], ['createdAt' => 'DESC'], 5);
        }
        // ROLE_TEACHER : Podcasts et enseignements
        elseif ($this->isGranted('ROLE_TEACHER')) {
            // Récupérer le profil de maître islamique associé
            $maitre = $this->maitreRepository->findOneBy(['user' => $currentUser]);
            
            if ($maitre) {
                $stats['podcasts'] = $this->podcastRepository->count(['author' => $currentUser]);
                $stats['enseignements'] = $this->enseignementRepository->count(['maitre' => $maitre]);
                
                $latestPodcasts = $this->podcastRepository->findBy(['author' => $currentUser], ['createdAt' => 'DESC'], 5);
                $latestEnseignements = $this->enseignementRepository->findBy(['maitre' => $maitre], ['createdAt' => 'DESC'], 5);
            } else {
                // Si pas de profil maître, proposer de le créer
                $this->addFlash('info', 'Veuillez créer votre profil de maître islamique pour commencer à publier des enseignements.');
            }
        }
        // ROLE_ARCHIVE_MANAGER : Uniquement les archives
        elseif ($this->isGranted('ROLE_ARCHIVE_MANAGER')) {
            $stats['archives'] = $this->archiveRepository->count([]);
            $latestArchives = $this->archiveRepository->findBy([], ['createdAt' => 'DESC'], 5);
        }

        // Calculer quelques métriques supplémentaires pour les rôles appropriés
        $metrics = $this->calculateMetrics($userRoles, $currentUser);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'latestArticles' => $latestArticles,
            'latestPodcasts' => $latestPodcasts,
            'latestArchives' => $latestArchives,
            'latestEnseignements' => $latestEnseignements,
            'latestUsers' => $latestUsers,
            'userRoles' => $userRoles,
            'userPermissions' => $userPermissions,
            'metrics' => $metrics,
            'currentUser' => $currentUser,
        ]);
    }

    private function getUserPermissions(array $roles): array
    {
        $permissions = [
            'canManageArticles' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_BLOG_MANAGER'),
            'canManagePodcasts' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_TEACHER'),
            'canManageArchives' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ARCHIVE_MANAGER'),
            'canManageEnseignements' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_TEACHER'),
            'canManageForum' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPERVISOR'),
            'canManageUsers' => $this->isGranted('ROLE_ADMIN'),
            'canViewStats' => $this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPERVISOR'),
            'canAccessEasyAdmin' => $this->isGranted('ROLE_ADMIN'),
        ];

        return $permissions;
    }

    private function calculateMetrics(array $roles, $user): array
    {
        $metrics = [
            'publishedContent' => 0,
            'pendingContent' => 0,
            'totalViews' => 0,
            'thisMonthContent' => 0,
        ];

        $currentMonth = new \DateTime('first day of this month');

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPERVISOR')) {
            // Métriques globales pour admin et superviseur
            $publishedArticles = $this->articleRepository->count(['isPublished' => true]);
            $publishedPodcasts = $this->podcastRepository->count(['isPublished' => true]);
            $publishedArchives = $this->archiveRepository->count(['isPublished' => true]);
            
            $metrics['publishedContent'] = $publishedArticles + $publishedPodcasts + $publishedArchives;
            
            $draftArticles = $this->articleRepository->count(['isPublished' => false]);
            $draftPodcasts = $this->podcastRepository->count(['isPublished' => false]);
            $draftArchives = $this->archiveRepository->count(['isPublished' => false]);
            
            $metrics['pendingContent'] = $draftArticles + $draftPodcasts + $draftArchives;
            
            // Contenu de ce mois
            $metrics['thisMonthContent'] = $this->articleRepository->countByDateRange($currentMonth, new \DateTime()) +
                                         $this->podcastRepository->countByDateRange($currentMonth, new \DateTime()) +
                                         $this->archiveRepository->countByDateRange($currentMonth, new \DateTime());
        }
        elseif ($this->isGranted('ROLE_TEACHER')) {
            // Métriques spécifiques au professeur
            $maitre = $this->maitreRepository->findOneBy(['user' => $user]);
            if ($maitre) {
                $userPodcasts = $this->podcastRepository->findBy(['author' => $user]);
                $userEnseignements = $this->enseignementRepository->findBy(['maitre' => $maitre]);
                
                $metrics['publishedContent'] = count(array_filter($userPodcasts, fn($p) => $p->isIsPublished())) +
                                              count(array_filter($userEnseignements, fn($e) => $e->isIsPublished()));
                                              
                $metrics['pendingContent'] = count(array_filter($userPodcasts, fn($p) => !$p->isIsPublished())) +
                                            count(array_filter($userEnseignements, fn($e) => !$e->isIsPublished()));
            }
        }
        elseif ($this->isGranted('ROLE_BLOG_MANAGER')) {
            // Métriques spécifiques aux articles
            $metrics['publishedContent'] = $this->articleRepository->count(['isPublished' => true]);
            $metrics['pendingContent'] = $this->articleRepository->count(['isPublished' => false]);
            $metrics['thisMonthContent'] = $this->articleRepository->countByDateRange($currentMonth, new \DateTime());
        }
        elseif ($this->isGranted('ROLE_ARCHIVE_MANAGER')) {
            // Métriques spécifiques aux archives
            $metrics['publishedContent'] = $this->archiveRepository->count(['isPublished' => true]);
            $metrics['pendingContent'] = $this->archiveRepository->count(['isPublished' => false]);
            $metrics['thisMonthContent'] = $this->archiveRepository->countByDateRange($currentMonth, new \DateTime());
        }

        return $metrics;
    }

    #[Route('/dashboard/quick-actions', name: 'admin_quick_actions')]
    public function quickActions(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $actions = [];
        
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_BLOG_MANAGER')) {
            $actions[] = [
                'title' => 'Nouvel Article',
                'url' => $this->generateUrl('admin_article_new'),
                'icon' => 'fas fa-newspaper',
                'color' => 'primary'
            ];
        }
        
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_TEACHER')) {
            $actions[] = [
                'title' => 'Nouveau Podcast',
                'url' => $this->generateUrl('admin_podcast_new'),
                'icon' => 'fas fa-microphone',
                'color' => 'success'
            ];
            
            $actions[] = [
                'title' => 'Nouvel Enseignement',
                'url' => $this->generateUrl('admin_enseignement_new'),
                'icon' => 'fas fa-chalkboard-teacher',
                'color' => 'info'
            ];
        }
        
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_ARCHIVE_MANAGER')) {
            $actions[] = [
                'title' => 'Nouvelle Archive',
                'url' => $this->generateUrl('admin_archive_new'),
                'icon' => 'fas fa-archive',
                'color' => 'warning'
            ];
        }
        
        return $this->json($actions);
    }
}
