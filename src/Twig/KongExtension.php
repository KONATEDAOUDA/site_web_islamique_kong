<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class KongExtension extends AbstractExtension
{
    private array $kongSettings;

    public function __construct(array $kongSettings)
    {
        $this->kongSettings = $kongSettings;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_duration', [$this, 'formatDuration']),
            new TwigFilter('excerpt', [$this, 'createExcerpt']),
            new TwigFilter('reading_time', [$this, 'calculateReadingTime']),
            new TwigFilter('format_filesize', [$this, 'formatFileSize']),
            new TwigFilter('arabic_date', [$this, 'formatArabicDate']),
            new TwigFilter('time_ago', [$this, 'timeAgo']),
            new TwigFilter('highlight_search', [$this, 'highlightSearchTerms'], ['is_safe' => ['html']]),
            new TwigFilter('slug', [$this, 'slugify']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('kong_config', [$this, 'getKongConfig']),
            new TwigFunction('admin_menu_active', [$this, 'isAdminMenuActive']),
            new TwigFunction('user_can_access', [$this, 'userCanAccess']),
            new TwigFunction('get_content_stats', [$this, 'getContentStats']),
            new TwigFunction('format_islamic_date', [$this, 'formatIslamicDate']),
            new TwigFunction('get_qibla_direction', [$this, 'getQiblaDirection']),
            new TwigFunction('prayer_times', [$this, 'getPrayerTimes']),
            new TwigFunction('hijri_date', [$this, 'getHijriDate']),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('audio_file', [$this, 'isAudioFile']),
            new TwigTest('video_file', [$this, 'isVideoFile']),
            new TwigTest('image_file', [$this, 'isImageFile']),
            new TwigTest('document_file', [$this, 'isDocumentFile']),
        ];
    }

    /**
     * Formate une durée en format lisible
     */
    public function formatDuration(?string $duration): string
    {
        if (!$duration) {
            return 'Non renseigné';
        }

        // Si c'est déjà formaté, on retourne tel quel
        if (preg_match('/\d+[hm]/', $duration)) {
            return $duration;
        }

        // Si c'est en secondes
        if (is_numeric($duration)) {
            $seconds = (int) $duration;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            if ($hours > 0) {
                return sprintf('%dh %02dm', $hours, $minutes);
            } elseif ($minutes > 0) {
                return sprintf('%dm %02ds', $minutes, $seconds);
            } else {
                return sprintf('%ds', $seconds);
            }
        }

        return $duration;
    }

    /**
     * Crée un extrait de texte
     */
    public function createExcerpt(string $text, int $length = 150): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        $excerpt = substr($text, 0, $length);
        $lastSpace = strrpos($excerpt, ' ');
        
        if ($lastSpace !== false) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '...';
    }

    /**
     * Calcule le temps de lecture estimé
     */
    public function calculateReadingTime(string $text): string
    {
        $wordCount = str_word_count(strip_tags($text));
        $minutes = ceil($wordCount / 200); // 200 mots par minute en moyenne
        
        if ($minutes <= 1) {
            return '1 min de lecture';
        }
        
        return $minutes . ' min de lecture';
    }

    /**
     * Formate la taille d'un fichier
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        
        return number_format($bytes / pow(1024, $power), 2, ',', ' ') . ' ' . $units[$power];
    }

    /**
     * Formate une date en arabe
     */
    public function formatArabicDate(\DateTimeInterface $date): string
    {
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latinNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $day = str_replace($latinNumerals, $arabicNumerals, $date->format('d'));
        $month = $arabicMonths[(int) $date->format('n')];
        $year = str_replace($latinNumerals, $arabicNumerals, $date->format('Y'));

        return "$day $month $year";
    }

    /**
     * Affiche le temps écoulé depuis une date
     */
    public function timeAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        } elseif ($diff->m > 0) {
            return $diff->m . ' mois';
        } elseif ($diff->d > 0) {
            return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'À l\'instant';
        }
    }

    /**
     * Surligne les termes de recherche
     */
    public function highlightSearchTerms(string $text, string $searchTerm): string
    {
        if (empty($searchTerm)) {
            return $text;
        }

        $terms = explode(' ', $searchTerm);
        foreach ($terms as $term) {
            $term = trim($term);
            if (strlen($term) > 2) {
                $text = preg_replace(
                    '/(' . preg_quote($term, '/') . ')/i',
                    '<mark class="search-highlight">$1</mark>',
                    $text
                );
            }
        }

        return $text;
    }

    /**
     * Crée un slug à partir d'un texte
     */
    public function slugify(string $text): string
    {
        // Remplacer les caractères spéciaux
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        
        return strtolower($text);
    }

    /**
     * Récupère une configuration Kong
     */
    public function getKongConfig(string $key = null)
    {
        if ($key === null) {
            return $this->kongSettings;
        }

        return $this->kongSettings[$key] ?? null;
    }

    /**
     * Vérifie si un menu admin est actif
     */
    public function isAdminMenuActive(string $routeName, string $currentRoute): bool
    {
        if ($routeName === $currentRoute) {
            return true;
        }

        // Vérifier les préfixes de route
        $routePrefixes = [
            'admin_article' => ['admin_article_index', 'admin_article_new', 'admin_article_edit', 'admin_article_show'],
            'admin_podcast' => ['admin_podcast_index', 'admin_podcast_new', 'admin_podcast_edit', 'admin_podcast_show'],
            'admin_archive' => ['admin_archive_index', 'admin_archive_new', 'admin_archive_edit', 'admin_archive_show'],
            'admin_enseignement' => ['admin_enseignement_index', 'admin_enseignement_new', 'admin_enseignement_edit', 'admin_enseignement_show'],
            'admin_forum' => ['admin_forum_index', 'admin_forum_topics', 'admin_forum_posts'],
        ];

        foreach ($routePrefixes as $prefix => $routes) {
            if ($routeName === $prefix && in_array($currentRoute, $routes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si un utilisateur peut accéder à une ressource
     */
    public function userCanAccess($user, string $resource): bool
    {
        if (!$user) {
            return false;
        }

        $roles = $user->getRoles();

        $permissions = [
            'articles' => ['ROLE_ADMIN', 'ROLE_BLOG_MANAGER', 'ROLE_SUPERVISOR'],
            'podcasts' => ['ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_SUPERVISOR'],
            'archives' => ['ROLE_ADMIN', 'ROLE_ARCHIVE_MANAGER', 'ROLE_SUPERVISOR'],
            'enseignements' => ['ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_SUPERVISOR'],
            'forum' => ['ROLE_ADMIN', 'ROLE_SUPERVISOR'],
            'users' => ['ROLE_ADMIN'],
            'settings' => ['ROLE_ADMIN'],
        ];

        if (!isset($permissions[$resource])) {
            return false;
        }

        return !empty(array_intersect($roles, $permissions[$resource]));
    }

    /**
     * Récupère des statistiques de contenu
     */
    public function getContentStats(): array
    {
        // Cette fonction devrait idéalement utiliser un service
        // Pour l'exemple, on retourne des données fictives
        return [
            'articles' => 0,
            'podcasts' => 0,
            'archives' => 0,
            'users' => 0,
        ];
    }

    /**
     * Formate une date islamique
     */
    public function formatIslamicDate(\DateTimeInterface $date): string
    {
        // Conversion approximative - pour une vraie application, utilisez une bibliothèque spécialisée
        $hijriMonths = [
            1 => 'محرم', 2 => 'صفر', 3 => 'ربيع الأول', 4 => 'ربيع الثاني',
            5 => 'جمادى الأولى', 6 => 'جمادى الثانية', 7 => 'رجب', 8 => 'شعبان',
            9 => 'رمضان', 10 => 'شوال', 11 => 'ذو القعدة', 12 => 'ذو الحجة'
        ];

        // Calcul approximatif de l'année hijri
        $gregorianYear = (int) $date->format('Y');
        $hijriYear = floor(($gregorianYear - 622) * 1.030684);

        return "التاريخ الهجري تقريبي: $hijriYear";
    }

    /**
     * Calcule la direction de la Qibla depuis Kong
     */
    public function getQiblaDirection(): string
    {
        // Kong est approximativement à 8.8°N, 5.0°W
        // La Kaaba est à 21.4225°N, 39.8262°E
        // Calcul simplifié : direction approximative depuis Kong vers La Mecque
        return "Nord-Est (≈ 75°)";
    }

    /**
     * Heures de prière approximatives pour Kong
     */
    public function getPrayerTimes(): array
    {
        return [
            'fajr' => '05:30',
            'dhuhr' => '12:30',
            'asr' => '15:45',
            'maghrib' => '18:30',
            'isha' => '19:45'
        ];
    }

    /**
     * Date hijri approximative
     */
    public function getHijriDate(): string
    {
        $now = new \DateTime();
        return $this->formatIslamicDate($now);
    }

    /**
     * Teste si un fichier est audio
     */
    public function isAudioFile(string $filename): bool
    {
        $audioExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $audioExtensions);
    }

    /**
     * Teste si un fichier est vidéo
     */
    public function isVideoFile(string $filename): bool
    {
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $videoExtensions);
    }

    /**
     * Teste si un fichier est image
     */
    public function isImageFile(string $filename): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    }

    /**
     * Teste si un fichier est document
     */
    public function isDocumentFile(string $filename): bool
    {
        $docExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $docExtensions);
    }
}
