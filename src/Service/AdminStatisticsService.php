<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\PodcastRepository;
use App\Repository\ArchiveRepository;
use App\Repository\EnseignementRepository;
use App\Repository\ForumTopicRepository;
use App\Repository\ForumPostRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\MaitreIslamiqueRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminStatisticsService
{
    private EntityManagerInterface $entityManager;
    private ArticleRepository $articleRepository;
    private PodcastRepository $podcastRepository;
    private ArchiveRepository $archiveRepository;
    private EnseignementRepository $enseignementRepository;
    private ForumTopicRepository $forumTopicRepository;
    private ForumPostRepository $forumPostRepository;
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private MaitreIslamiqueRepository $maitreRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ArticleRepository $articleRepository,
        PodcastRepository $podcastRepository,
        ArchiveRepository $archiveRepository,
        EnseignementRepository $enseignementRepository,
        ForumTopicRepository $forumTopicRepository,
        ForumPostRepository $forumPostRepository,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        MaitreIslamiqueRepository $maitreRepository
    ) {
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
        $this->podcastRepository = $podcastRepository;
        $this->archiveRepository = $archiveRepository;
        $this->enseignementRepository = $enseignementRepository;
        $this->forumTopicRepository = $forumTopicRepository;
        $this->forumPostRepository = $forumPostRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->maitreRepository = $maitreRepository;
    }

    /**
     * Obtenir les statistiques globales pour l'admin
     */
    public function getGlobalStatistics(): array
    {
        return [
            'content' => [
                'articles' => [
                    'total' => $this->articleRepository->count([]),
                    'published' => $this->articleRepository->count(['isPublished' => true]),
                    'featured' => $this->articleRepository->count(['isFeatured' => true]),
                    'this_month' => $this->getContentCountThisMonth('Article'),
                ],
                'podcasts' => [
                    'total' => $this->podcastRepository->count([]),
                    'published' => $this->podcastRepository->count(['isPublished' => true]),
                    'audio' => $this->podcastRepository->count(['type' => 'audio']),
                    'video' => $this->podcastRepository->count(['type' => 'video']),
                    'this_month' => $this->getContentCountThisMonth('Podcast'),
                ],
                'archives' => [
                    'total' => $this->archiveRepository->count([]),
                    'published' => $this->archiveRepository->count(['isPublished' => true]),
                    'restricted' => $this->archiveRepository->count(['isRestricted' => true]),
                    'by_type' => $this->getArchivesByType(),
                    'this_month' => $this->getContentCountThisMonth('Archive'),
                ],
                'enseignements' => [
                    'total' => $this->enseignementRepository->count([]),
                    'published' => $this->enseignementRepository->count(['isPublished' => true]),
                    'open_access' => $this->enseignementRepository->count(['isOpenAccess' => true]),
                    'by_category' => $this->getEnseignementsByCategory(),
                    'by_level' => $this->getEnseignementsByLevel(),
                    'this_month' => $this->getContentCountThisMonth('Enseignement'),
                ],
            ],
            'community' => [
                'users' => [
                    'total' => $this->userRepository->count([]),
                    'active_this_month' => $this->getActiveUsersThisMonth(),
                    'new_this_month' => $this->getNewUsersThisMonth(),
                    'by_role' => $this->getUsersByRole(),
                ],
                'maitres' => [
                    'total' => $this->maitreRepository->count([]),
                    'active' => $this->getActiveMaitres(),
                ],
                'forum' => [
                    'topics' => $this->forumTopicRepository->count([]),
                    'posts' => $this->forumPostRepository->count([]),
                    'active_topics' => $this->forumTopicRepository->count(['isActive' => true]),
                    'this_month' => [
                        'topics' => $this->getForumActivityThisMonth('topic'),
                        'posts' => $this->getForumActivityThisMonth('post'),
                    ],
                ],
                'comments' => [
                    'total' => $this->commentRepository->count([]),
                    'approved' => $this->commentRepository->count(['isApproved' => true]),
                    'pending' => $this->commentRepository->count(['isApproved' => false]),
                    'this_month' => $this->getCommentsThisMonth(),
                ],
            ],
            'trends' => [
                'content_growth' => $this->getContentGrowthTrend(),
                'user_engagement' => $this->getUserEngagementTrend(),
                'popular_categories' => $this->getPopularCategories(),
            ],
        ];
    }

    /**
     * Obtenir les statistiques pour un utilisateur spécifique
     */
    public function getUserStatistics(User $user): array
    {
        $userRoles = $user->getRoles();
        $stats = [];

        // Statistiques communes
        $stats['user_info'] = [
            'roles' => $userRoles,
            'created_at' => $user->getCreatedAt(),
            'last_activity' => $user->getLastActivityAt() ?? null,
        ];

        // Statistiques pour les gestionnaires de blog
        if (in_array('ROLE_BLOG_MANAGER', $userRoles) || in_array('ROLE_ADMIN', $userRoles)) {
            $stats['articles'] = [
                'authored' => $this->articleRepository->count(['author' => $user]),
                'published' => $this->articleRepository->count(['author' => $user, 'isPublished' => true]),
                'drafts' => $this->articleRepository->count(['author' => $user, 'isPublished' => false]),
                'comments_received' => $this->getCommentsOnUserContent($user, 'article'),
            ];
        }

        // Statistiques pour les enseignants
        if (in_array('ROLE_TEACHER', $userRoles) || in_array('ROLE_ADMIN', $userRoles)) {
            $maitre = $this->maitreRepository->findOneBy(['user' => $user]);
            
            $stats['podcasts'] = [
                'authored' => $this->podcastRepository->count(['author' => $user]),
                'published' => $this->podcastRepository->count(['author' => $user, 'isPublished' => true]),
                'total_duration' => $this->getTotalPodcastDuration($user),
            ];

            if ($maitre) {
                $stats['enseignements'] = [
                    'created' => $this->enseignementRepository->count(['maitre' => $maitre]),
                    'published' => $this->enseignementRepository->count(['maitre' => $maitre, 'isPublished' => true]),
                    'categories' => $this->getUserEnseignementCategories($maitre),
                ];
            }
        }

        // Activité sur le forum
        $stats['forum_activity'] = [
            'topics_created' => $this->forumTopicRepository->count(['author' => $user]),
            'posts_created' => $this->forumPostRepository->count(['author' => $user]),
            'comments_made' => $this->commentRepository->count(['author' => $user]),
        ];

        return $stats;
    }

    /**
     * Obtenir les données pour les graphiques du dashboard
     */
    public function getDashboardChartData(): array
    {
        return [
            'content_evolution' => $this->getContentEvolutionData(),
            'user_registration' => $this->getUserRegistrationData(),
            'content_by_category' => $this->getContentByCategoryData(),
            'engagement_metrics' => $this->getEngagementMetricsData(),
        ];
    }

    /**
     * Obtenir le contenu le plus populaire
     */
    public function getPopularContent(int $limit = 10): array
    {
        return [
            'articles' => $this->getPopularArticles($limit),
            'podcasts' => $this->getPopularPodcasts($limit),
            'enseignements' => $this->getPopularEnseignements($limit),
        ];
    }

    /**
     * Obtenir les métriques de performance
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'content_approval_rate' => $this->getContentApprovalRate(),
            'user_retention_rate' => $this->getUserRetentionRate(),
            'average_content_per_user' => $this->getAverageContentPerUser(),
            'forum_engagement_rate' => $this->getForumEngagementRate(),
        ];
    }

    // Méthodes privées pour les calculs spécifiques

    private function getContentCountThisMonth(string $entityName): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(e.id)')
           ->from('App\Entity\\' . $entityName, 'e')
           ->where('e.createdAt BETWEEN :start AND :end')
           ->setParameter('start', $startOfMonth)
           ->setParameter('end', $endOfMonth);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getArchivesByType(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a.type, COUNT(a.id) as count')
           ->from('App\Entity\Archive', 'a')
           ->groupBy('a.type');

        $results = $qb->getQuery()->getResult();
        $data = [];
        foreach ($results as $result) {
            $data[$result['type']] = $result['count'];
        }
        return $data;
    }

    private function getEnseignementsByCategory(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.category, COUNT(e.id) as count')
           ->from('App\Entity\Enseignement', 'e')
           ->groupBy('e.category');

        $results = $qb->getQuery()->getResult();
        $data = [];
        foreach ($results as $result) {
            $data[$result['category']] = $result['count'];
        }
        return $data;
    }

    private function getEnseignementsByLevel(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.level, COUNT(e.id) as count')
           ->from('App\Entity\Enseignement', 'e')
           ->groupBy('e.level');

        $results = $qb->getQuery()->getResult();
        $data = [];
        foreach ($results as $result) {
            $data[$result['level']] = $result['count'];
        }
        return $data;
    }

    private function getActiveUsersThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        
        // Compter les utilisateurs qui ont créé du contenu ce mois-ci
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(DISTINCT u.id)')
           ->from('App\Entity\User', 'u')
           ->leftJoin('u.articles', 'a')
           ->leftJoin('u.podcasts', 'p')
           ->leftJoin('u.forumPosts', 'fp')
           ->leftJoin('u.comments', 'c')
           ->where('a.createdAt >= :start OR p.createdAt >= :start OR fp.createdAt >= :start OR c.createdAt >= :start')
           ->setParameter('start', $startOfMonth);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getNewUsersThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        return $this->userRepository->count(['createdAt' => $startOfMonth]);
    }

    private function getUsersByRole(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u.roles')
           ->from('App\Entity\User', 'u');

        $results = $qb->getQuery()->getResult();
        $roleCounts = [];

        foreach ($results as $result) {
            $roles = json_decode($result['roles'], true);
            foreach ($roles as $role) {
                $roleCounts[$role] = ($roleCounts[$role] ?? 0) + 1;
            }
        }

        return $roleCounts;
    }

    private function getActiveMaitres(): int
    {
        // Maîtres qui ont publié au moins un enseignement
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(DISTINCT m.id)')
           ->from('App\Entity\MaitreIslamique', 'm')
           ->join('m.enseignements', 'e')
           ->where('e.isPublished = true');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getForumActivityThisMonth(string $type): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $entityName = $type === 'topic' ? 'ForumTopic' : 'ForumPost';

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(e.id)')
           ->from('App\Entity\\' . $entityName, 'e')
           ->where('e.createdAt >= :start')
           ->setParameter('start', $startOfMonth);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getCommentsThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        return $this->commentRepository->count(['createdAt' => $startOfMonth]);
    }

    private function getContentGrowthTrend(): array
    {
        // Données des 12 derniers mois
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTime("-{$i} months");
            $monthStart = $date->format('Y-m-01 00:00:00');
            $monthEnd = $date->format('Y-m-t 23:59:59');

            $articles = $this->getContentCountForPeriod('Article', $monthStart, $monthEnd);
            $podcasts = $this->getContentCountForPeriod('Podcast', $monthStart, $monthEnd);
            $enseignements = $this->getContentCountForPeriod('Enseignement', $monthStart, $monthEnd);

            $data[] = [
                'month' => $date->format('Y-m'),
                'articles' => $articles,
                'podcasts' => $podcasts,
                'enseignements' => $enseignements,
                'total' => $articles + $podcasts + $enseignements,
            ];
        }

        return $data;
    }

    private function getContentCountForPeriod(string $entityName, string $start, string $end): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(e.id)')
           ->from('App\Entity\\' . $entityName, 'e')
           ->where('e.createdAt BETWEEN :start AND :end')
           ->setParameter('start', new \DateTime($start))
           ->setParameter('end', new \DateTime($end));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getUserEngagementTrend(): array
    {
        // Calculer l'engagement utilisateur (commentaires, posts forum, etc.)
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTime("-{$i} months");
            $monthStart = $date->format('Y-m-01 00:00:00');
            $monthEnd = $date->format('Y-m-t 23:59:59');

            $comments = $this->getCommentsForPeriod($monthStart, $monthEnd);
            $forumPosts = $this->getForumPostsForPeriod($monthStart, $monthEnd);

            $data[] = [
                'month' => $date->format('Y-m'),
                'comments' => $comments,
                'forum_posts' => $forumPosts,
                'total_engagement' => $comments + $forumPosts,
            ];
        }

        return $data;
    }

    private function getCommentsForPeriod(string $start, string $end): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(c.id)')
           ->from('App\Entity\Comment', 'c')
           ->where('c.createdAt BETWEEN :start AND :end')
           ->setParameter('start', new \DateTime($start))
           ->setParameter('end', new \DateTime($end));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getForumPostsForPeriod(string $start, string $end): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(fp.id)')
           ->from('App\Entity\ForumPost', 'fp')
           ->where('fp.createdAt BETWEEN :start AND :end')
           ->setParameter('start', new \DateTime($start))
           ->setParameter('end', new \DateTime($end));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getPopularCategories(): array
    {
        // Combiner les catégories d'articles et d'enseignements
        $articleCategories = $this->getArticleCategories();
        $enseignementCategories = $this->getEnseignementsByCategory();

        $allCategories = array_merge($articleCategories, $enseignementCategories);
        arsort($allCategories);

        return array_slice($allCategories, 0, 10, true);
    }

    private function getArticleCategories(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c.name, COUNT(a.id) as count')
           ->from('App\Entity\Article', 'a')
           ->join('a.category', 'c')
           ->groupBy('c.name');

        $results = $qb->getQuery()->getResult();
        $data = [];
        foreach ($results as $result) {
            $data[$result['name']] = $result['count'];
        }
        return $data;
    }

    // Autres méthodes privées...
    private function getContentEvolutionData(): array { return []; }
    private function getUserRegistrationData(): array { return []; }
    private function getContentByCategoryData(): array { return []; }
    private function getEngagementMetricsData(): array { return []; }
    private function getPopularArticles(int $limit): array { return []; }
    private function getPopularPodcasts(int $limit): array { return []; }
    private function getPopularEnseignements(int $limit): array { return []; }
    private function getContentApprovalRate(): float { return 0.0; }
    private function getUserRetentionRate(): float { return 0.0; }
    private function getAverageContentPerUser(): float { return 0.0; }
    private function getForumEngagementRate(): float { return 0.0; }
    private function getCommentsOnUserContent(User $user, string $type): int { return 0; }
    private function getTotalPodcastDuration(User $user): string { return '0h'; }
    private function getUserEnseignementCategories($maitre): array { return []; }
}
