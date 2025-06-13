<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Ici, nous allons simuler les données pour le moment
        // Dans une véritable application, ces données proviendraient de la base de données
        
        $latestArticles = [
            [
                'title' => 'L\'histoire de la mosquée de Kong',
                'excerpt' => 'Découvrez l\'histoire fascinante de la mosquée de Kong, un joyau architectural du XVIIIe siècle...',
                'thumbnail' => 'images/grande_mosquee_kong_old.jpg',
                'slug' => 'histoire-mosquee-kong',
                'createdAt' => new \DateTime('2023-08-15')
            ],
            [
                'title' => 'Les manuscrits islamiques de Kong',
                'excerpt' => 'Kong abrite une collection unique de manuscrits islamiques anciens qui témoignent de la richesse intellectuelle...',
                'thumbnail' => 'images/manuscripts-thumbnail.jpg',
                'slug' => 'manuscrits-islamiques-kong',
                'createdAt' => new \DateTime('2023-09-01')
            ],
            [
                'title' => 'Traditions et pratiques islamiques à Kong',
                'excerpt' => 'Explorez les traditions et pratiques islamiques uniques qui ont façonné la culture de Kong au fil des siècles...',
                'thumbnail' => 'images/traditions-thumbnail.jpg',
                'slug' => 'traditions-pratiques-islamiques-kong',
                'createdAt' => new \DateTime('2023-09-15')
            ]
        ];
        
        $latestPodcasts = [
            [
                'title' => 'Entretien avec l\'Imam de Kong',
                'description' => 'Dans ce podcast, l\'Imam de Kong partage sa vision de l\'islam contemporain et les défis de la communauté.',
                'audioFile' => 'podcasts/imam-interview.mp3',
                'slug' => 'entretien-imam-kong',
                'createdAt' => new \DateTime('2023-08-20')
            ],
            [
                'title' => 'L\'héritage soufi à Kong',
                'description' => 'Découvrez l\'influence du soufisme sur la pratique islamique à Kong et son impact sur la spiritualité locale.',
                'audioFile' => 'podcasts/soufi-heritage.mp3',
                'slug' => 'heritage-soufi-kong',
                'createdAt' => new \DateTime('2023-09-05')
            ],
            [
                'title' => 'Éducation islamique à Kong',
                'description' => 'Comment l\'éducation islamique a évolué à Kong et son rôle dans la transmission des connaissances religieuses.',
                'audioFile' => 'podcasts/education-islamique.mp3',
                'slug' => 'education-islamique-kong',
                'createdAt' => new \DateTime('2023-09-20')
            ]
        ];

        $teachers = [
            [
                'name' => 'Imam de Kong',
                'specialty' => 'Partage sa vision',
                'slug' => 'entretien-imam-kong',
                'createdAt' => new \DateTime('2023-08-20')
            ],
            [
                'name' => ' Adjoint a Imam de Kong',
                'specialty' => 'la pratique islamique',
                'slug' => 'heritage-soufi-kong',
            ],
            [
                'name' => 'Maitre islamique de Kong',
                'specialty' => 'éducation islamique',
                'slug' => 'education-islamique-kong',
            ]
        ];

        $relevantArchives = [
            [
                'title' => 'Documents historiques sur la fondation de Kong',
                'description' => 'Collection de documents historiques sur la fondation de la ville de Kong et son développement comme centre islamique.',
                'type' => 'article',
                'thumbnail' => 'images/archives/documents-historiques.jpg',
                'slug' => 'documents-historiques-fondation-kong',
                'year' => '2022',
                'createdAt' => new \DateTime('2022-10-15')
            ],
            [
                'title' => 'Chants soufis traditionnels de Kong',
                'description' => 'Enregistrements rares de chants soufis traditionnels pratiqués à Kong depuis des générations.',
                'type' => 'audio',
                'audioFile' => 'archives/chants-soufis.mp3',
                'slug' => 'chants-soufis-traditionnels-kong',
                'year' => '2022',
                'createdAt' => new \DateTime('2022-11-20')
            ],
            [
                'title' => 'La vie quotidienne des érudits islamiques de Kong',
                'description' => 'Documentaire sur la vie quotidienne des érudits islamiques de Kong et leur rôle dans la communauté.',
                'type' => 'video',
                'videoFile' => 'archives/erudits-kong.mp4',
                'slug' => 'vie-quotidienne-erudits-islamiques-kong',
                'year' => '2023',
                'createdAt' => new \DateTime('2023-01-10')
            ]
        ];
        
        $siteHistory = "Kong a été pendant des siècles un important centre islamique en Afrique de l'Ouest.
         Cette plateforme vise à préserver et partager le riche patrimoine islamique de Kong, ses traditions,
         son savoir et ses pratiques qui ont façonné l'identité de cette région historique.";

        
        return $this->render('frontend/home/index.html.twig', [
            'latestArticles' => $latestArticles,
            'latestPodcasts' => $latestPodcasts,
            'relevantArchives' => $relevantArchives,
            'siteHistory' => $siteHistory,
            'teachers' => $teachers
        ]);

    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        // Cette fonction sera développée pour gérer le formulaire de contact
        return $this->render('frontend/contact/index.html.twig');
    }
    
    #[Route('/a-propos', name: 'app_about')]
    public function about(): Response
    {
        // Cette fonction affichera les informations sur la plateforme
        return $this->render('frontend/about/index.html.twig');
    }

    // =============================== creation  des routes de test =============================

    #[Route(path: '/archive', name: 'app_archive')]
    public function archive()
    {
        // app_archive
    }

    #[Route(path: '/archive_show', name: 'app_archive_show')]
    public function archive_show()
    {
        // app_archive_show
    }

    #[Route(path: '/teacher', name: 'app_teacher')]
    public function teacher()
    {
        // app_teacher
    }

    #[Route(path: '/teacher_show', name: 'app_teacher_show')]
    public function teacher_show()
    {
        // app_teacher_show
    }

    #[Route(path: '/forum', name: 'app_forum')]
    public function forum()
    {
        // app_forum
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login()
    {
        //app_login
    }

    #[Route(path: '/register', name: 'app_register')]
    public function register()
    {
        //register
    }

    #[Route(path: '/terms', name: 'app_terms')]
    public function terms()
    {
        //app_terms
    }

    #[Route(path: '/privacy', name: 'app_privacy')]
    public function privacy()
    {
        //app_privacy
    }

    #[Route(path: '/profile', name: 'app_profile')]
    public function profile()
    {
        //profile app_favorites
    }

    #[Route(path: '/favorites', name: 'app_favorites')]
    public function favorites()
    {
        // favorites
    }

   
    
    
}
