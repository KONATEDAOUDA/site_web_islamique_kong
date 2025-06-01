<?php

namespace App\Twig;

use App\Service\AdminStatisticsService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminExtension extends AbstractExtension
{
    private Security $security;
    private UrlGeneratorInterface $urlGenerator;
    private ?AdminStatisticsService $statisticsService;

    public function __construct(
        Security $security,
        UrlGeneratorInterface $urlGenerator,
        AdminStatisticsService $statisticsService = null
    ) {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->statisticsService = $statisticsService;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('admin_status_badge', [$this, 'renderStatusBadge'], ['is_safe' => ['html']]),
            new TwigFilter('admin_role_label', [$this, 'renderRoleLabel'], ['is_safe' => ['html']]),
            new TwigFilter('admin_file_icon', [$this, 'getFileIcon'], ['is_safe' => ['html']]),
            new TwigFilter('admin_progress_bar', [$this, 'renderProgressBar'], ['is_safe' => ['html']]),
            new TwigFilter('admin_avatar', [$this, 'renderUserAvatar'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_sidebar_menu', [$this, 'generateSidebarMenu'], ['is_safe' => ['html']]),
            new TwigFunction('admin_breadcrumb', [$this, 'generateBreadcrumb'], ['is_safe' => ['html']]),
            new TwigFunction('admin_quick_stats', [$this, 'getQuickStats']),
            new TwigFunction('admin_user_permissions', [$this, 'getUserPermissions']),
            new TwigFunction('admin_notification_count', [$this, 'getNotificationCount']),
            new TwigFunction('admin_recent_activity', [$this, 'getRecentActivity']),
            new TwigFunction('admin_system_info', [$this, 'getSystemInfo']),
        ];
    }

    /**
     * Génère un badge de statut selon le type
     */
    public function renderStatusBadge(bool $status, string $type = 'published'): string
    {
        $badges = [
            'published' => [
                true => '<span class="badge badge-success">Publié</span>',
                false => '<span class="badge badge-warning">Brouillon</span>'
            ],
            'active' => [
                true => '<span class="badge badge-success">Actif</span>',
                false => '<span class="badge badge-danger">Inactif</span>'
            ],
            'approved' => [
                true => '<span class="badge badge-success">Approuvé</span>',
                false => '<span class="badge badge-warning">En attente</span>'
            ],
            'featured' => [
                true => '<span class="badge badge-primary">À la une</span>',
                false => ''
            ]
        ];

        return $badges[$type][$status] ?? '';
    }

    /**
     * Génère un label pour un rôle utilisateur
     */
    public function renderRoleLabel(array $roles): string
    {
        $roleLabels = [
            'ROLE_ADMIN' => '<span class="badge badge-danger"><i class="fas fa-crown"></i> Administrateur</span>',
            'ROLE_SUPERVISOR' => '<span class="badge badge-info"><i class="fas fa-eye"></i> Superviseur</span>',
            'ROLE_BLOG_MANAGER' => '<span class="badge badge-primary"><i class="fas fa-newspaper"></i> Gestionnaire Blog</span>',
            'ROLE_TEACHER' => '<span class="badge badge-success"><i class="fas fa-chalkboard-teacher"></i> Enseignant</span>',
            'ROLE_ARCHIVE_MANAGER' => '<span class="badge badge-warning"><i class="fas fa-archive"></i> Gestionnaire Archives</span>',
            'ROLE_USER' => '<span class="badge badge-secondary"><i class="fas fa-user"></i> Utilisateur</span>'
        ];

        $html = '';
        foreach ($roles as $role) {
            if (isset($roleLabels[$role])) {
                $html .= $roleLabels[$role] . ' ';
            }
        }

        return trim($html) ?: $roleLabels['ROLE_USER'];
    }

    /**
     * Retourne l'icône appropriée pour un type de fichier
     */
    public function getFileIcon(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $icons = [
            // Images
            'jpg' => '<i class="fas fa-file-image text-info"></i>',
            'jpeg' => '<i class="fas fa-file-image text-info"></i>',
            'png' => '<i class="fas fa-file-image text-info"></i>',
            'gif' => '<i class="fas fa-file-image text-info"></i>',
            'webp' => '<i class="fas fa-file-image text-info"></i>',
            
            // Audio
            'mp3' => '<i class="fas fa-file-audio text-success"></i>',
            'wav' => '<i class="fas fa-file-audio text-success"></i>',
            'ogg' => '<i class="fas fa-file-audio text-success"></i>',
            
            // Vidéo
            'mp4' => '<i class="fas fa-file-video text-primary"></i>',
            'avi' => '<i class="fas fa-file-video text-primary"></i>',
            'mov' => '<i class="fas fa-file-video text-primary"></i>',
            
            // Documents
            'pdf' => '<i class="fas fa-file-pdf text-danger"></i>',
            'doc' => '<i class="fas fa-file-word text-primary"></i>',
            'docx' => '<i class="fas fa-file-word text-primary"></i>',
            'txt' => '<i class="fas fa-file-alt text-secondary"></i>',
            
            // Archives
            'zip' => '<i class="fas fa-file-archive text-warning"></i>',
            'rar' => '<i class="fas fa-file-archive text-warning"></i>',
        ];

        return $icons[$extension] ?? '<i class="fas fa-file text-muted"></i>';
    }

    /**
     * Génère une barre de progression
     */
    public function renderProgressBar(int $value, int $max = 100, string $color = 'primary'): string
    {
        $percentage = $max > 0 ? round(($value / $max) * 100) : 0;
        
        return sprintf(
            '<div class="progress" style="height: 20px;">
                <div class="progress-bar bg-%s" role="progressbar" style="width: %d%%" aria-valuenow="%d" aria-valuemin="0" aria-valuemax="%d">
                    %d%%
                </div>
            </div>',
            $color,
            $percentage,
            $value,
            $max,
            $percentage
        );
    }

    /**
     * Génère un avatar utilisateur
     */
    public function renderUserAvatar($user, int $size = 32): string
    {
        if (!$user) {
            return sprintf(
                '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: %dpx; height: %dpx;">
                    <i class="fas fa-user text-white"></i>
                </div>',
                $size, $size
            );
        }

        if ($user->getProfilePicture()) {
            return sprintf(
                '<img src="/uploads/users/%s" alt="%s" class="img-circle" style="width: %dpx; height: %dpx;">',
                $user->getProfilePicture(),
                htmlspecialchars($user->getFullName()),
                $size, $size
            );
        }

        $initials = '';
        if ($user->getFirstname()) {
            $initials .= strtoupper(substr($user->getFirstname(), 0, 1));
        }
        if ($user->getLastname()) {
            $initials .= strtoupper(substr($user->getLastname(), 0, 1));
        }

        return sprintf(
            '<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: %dpx; height: %dpx; font-size: %dpx;">
                %s
            </div>',
            $size, $size, $size * 0.4, $initials ?: 'U'
        );
    }

    /**
     * Génère le menu de la sidebar selon les permissions
     */
    public function generateSidebarMenu(): string
    {
        $user = $this->security->getUser();
        if (!$user) {
            return '';
        }

        $menuItems = [];

        // Dashboard
        $menuItems[] = [
            'title' => 'Tableau de bord',
            'route' => 'admin_dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'roles' => ['ROLE_USER']
        ];

        // Articles
        if ($this->security->isGranted('ROLE_BLOG_MANAGER') || $this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = [
                'title' => 'Articles',
                'icon' => 'fas fa-newspaper',
                'children' => [
                    ['title' => 'Liste', 'route' => 'admin_article_index'],
                    ['title' => 'Nouveau', 'route' => 'admin_article_new'],
                ]
            ];
        }

        // Podcasts
        if ($this->security->isGranted('ROLE_TEACHER') || $this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = [
                'title' => 'Podcasts',
                'icon' => 'fas fa-microphone',
                'children' => [
                    ['title' => 'Liste', 'route' => 'admin_podcast_index'],
                    ['title' => 'Nouveau', 'route' => 'admin_podcast_new'],
                ]
            ];
        }

        // Archives
        if ($this->security->isGranted('ROLE_ARCHIVE_MANAGER') || $this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = [
                'title' => 'Archives',
                'icon' => 'fas fa-archive',
                'children' => [
                    ['title' => 'Liste', 'route' => 'admin_archive_index'],
                    ['title' => 'Nouvelle', 'route' => 'admin_archive_new'],
                ]
            ];
        }

        // Enseignements
        if ($this->security->isGranted('ROLE_TEACHER') || $this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = [
                'title' => 'Enseignements',
                'icon' => 'fas fa-chalkboard-teacher',
                'children' => [
                    ['title' => 'Liste', 'route' => 'admin_enseignement_index'],
                    ['title' => 'Nouveau', 'route' => 'admin_enseignement_new'],
                ]
            ];
        }

        // Forum
        if ($this->security->isGranted('ROLE_SUPERVISOR') || $this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = [
                'title' => 'Forum',
                'icon' => 'fas fa-comments',
                'children' => [
                    ['title' => 'Vue d\'ensemble', 'route' => 'admin_forum_index'],
                    ['title' => 'Sujets', 'route' => 'admin_forum_topics'],
                    ['title' => 'Messages', 'route' => 'admin_forum_posts'],
                ]
            ];
        }

        // Administration
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $menuItems[] = [
                'title' => 'Administration',
                'icon' => 'fas fa-cogs',
                'children' => [
                    ['title' => 'EasyAdmin', 'route' => 'admin'],
                    ['title' => 'Utilisateurs', 'url' => '/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CUserCrudController'],
                    ['title' => 'Paramètres', 'url' => '/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CParamsCrudController'],
                ]
            ];
        }

        return $this->renderMenuItems($menuItems);
    }

    /**
     * Rendu des éléments de menu
     */
    private function renderMenuItems(array $items): string
    {
        $html = '';
        
        foreach ($items as $item) {
            if (isset($item['children'])) {
                $html .= sprintf(
                    '<li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="%s nav-icon"></i>
                            <p>%s <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">',
                    $item['icon'], $item['title']
                );

                foreach ($item['children'] as $child) {
                    $url = isset($child['route']) ? $this->urlGenerator->generate($child['route']) : $child['url'];
                    $html .= sprintf(
                        '<li class="nav-item">
                            <a href="%s" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>%s</p>
                            </a>
                        </li>',
                        $url, $child['title']
                    );
                }

                $html .= '</ul></li>';
            } else {
                $url = $this->urlGenerator->generate($item['route']);
                $html .= sprintf(
                    '<li class="nav-item">
                        <a href="%s" class="nav-link">
                            <i class="%s nav-icon"></i>
                            <p>%s</p>
                        </a>
                    </li>',
                    $url, $item['icon'], $item['title']
                );
            }
        }

        return $html;
    }

    /**
     * Génère le fil d'Ariane
     */
    public function generateBreadcrumb(string $currentPage, array $parents = []): string
    {
        $html = '<ol class="breadcrumb float-sm-right">';
        
        foreach ($parents as $parent) {
            $html .= sprintf(
                '<li class="breadcrumb-item"><a href="%s">%s</a></li>',
                $parent['url'], $parent['title']
            );
        }
        
        $html .= sprintf('<li class="breadcrumb-item active">%s</li>', $currentPage);
        $html .= '</ol>';

        return $html;
    }

    /**
     * Récupère les statistiques rapides
     */
    public function getQuickStats(): array
    {
        if (!$this->statisticsService) {
            return [];
        }

        $user = $this->security->getUser();
        return $this->statisticsService->getUserStatistics($user);
    }

    /**
     * Récupère les permissions de l'utilisateur
     */
    public function getUserPermissions(): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [];
        }

        return [
            'canManageArticles' => $this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_BLOG_MANAGER'),
            'canManagePodcasts' => $this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_TEACHER'),
            'canManageArchives' => $this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_ARCHIVE_MANAGER'),
            'canManageEnseignements' => $this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_TEACHER'),
            'canManageForum' => $this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_SUPERVISOR'),
            'canManageUsers' => $this->security->isGranted('ROLE_ADMIN'),
            'canAccessEasyAdmin' => $this->security->isGranted('ROLE_ADMIN'),
        ];
    }

    /**
     * Compte les notifications non lues
     */
    public function getNotificationCount(): int
    {
        // À implémenter selon votre système de notifications
        return 0;
    }

    /**
     * Récupère l'activité récente
     */
    public function getRecentActivity(): array
    {
        // À implémenter selon vos besoins
        return [];
    }

    /**
     * Informations système
     */
    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'server_time' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }
}
