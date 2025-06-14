<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PageController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private CategoryRepository $categoryRepository
    ) {}

    public function gallery(): Response
    {
        return $this->render('frontend/pages/gallery.html.twig');
    }

    public function prayerTimes(): Response
    {
        // Horaires de prière pour Kong (exemple)
        $prayerTimes = [
            'fajr' => '05:30',
            'sunrise' => '06:45',
            'dhuhr' => '12:15',
            'asr' => '15:45',
            'maghrib' => '18:20',
            'isha' => '19:30'
        ];

        // Dates islamiques importantes (exemple)
        $islamicDates = [
            [
                'name' => 'Ramadan 2025',
                'start' => '2025-02-28',
                'end' => '2025-03-30',
                'type' => 'ramadan'
            ],
            [
                'name' => 'Aïd al-Fitr',
                'date' => '2025-03-30',
                'type' => 'eid'
            ],
            [
                'name' => 'Aïd al-Adha',
                'date' => '2025-06-06',
                'type' => 'eid'
            ],
            [
                'name' => 'Muharram (Nouvel An Islamique)',
                'date' => '2025-07-07',
                'type' => 'new_year'
            ]
        ];

        return $this->render('frontend/pages/prayer_times.html.twig', [
            'prayerTimes' => $prayerTimes,
            'islamicDates' => $islamicDates,
            'location' => 'Kong, Côte d\'Ivoire',
            'timezone' => 'UTC+0'
        ]);
    }

    public function faq(): Response
    {
        $faqs = [
            [
                'category' => 'Général',
                'questions' => [
                    [
                        'question' => 'Qu\'est-ce que le portail islamique de Kong ?',
                        'answer' => 'Le portail islamique de Kong est une plateforme numérique dédiée à la préservation et au partage du patrimoine islamique de Kong, ville historique de Côte d\'Ivoire.'
                    ],
                    [
                        'question' => 'Comment puis-je contribuer au site ?',
                        'answer' => 'Vous pouvez contribuer en partageant des documents historiques, des témoignages, des photos ou en nous contactant pour proposer du contenu.'
                    ],
                    [
                        'question' => 'Le site est-il gratuit ?',
                        'answer' => 'Oui, l\'accès au contenu du site est entièrement gratuit. Notre mission est de rendre accessible le patrimoine islamique de Kong à tous.'
                    ]
                ]
            ],
            [
                'category' => 'Archives',
                'questions' => [
                    [
                        'question' => 'Comment accéder aux archives ?',
                        'answer' => 'Les archives sont accessibles via la section "Archives" du site. Certains documents peuvent nécessiter une inscription gratuite.'
                    ],
                    [
                        'question' => 'Puis-je télécharger les documents ?',
                        'answer' => 'Oui, de nombreux documents sont téléchargeables. Les droits de téléchargement sont indiqués pour chaque document.'
                    ],
                    [
                        'question' => 'Comment citer les sources du site ?',
                        'answer' => 'Chaque document dispose d\'informations de citation. Vous pouvez aussi nous contacter pour des références académiques précises.'
                    ]
                ]
            ],
            [
                'category' => 'Technique',
                'questions' => [
                    [
                        'question' => 'Problème de connexion ou d\'affichage ?',
                        'answer' => 'Vérifiez votre connexion internet et videz le cache de votre navigateur. Si le problème persiste, contactez-nous.'
                    ],
                    [
                        'question' => 'Le site est-il optimisé pour mobile ?',
                        'answer' => 'Oui, le site est entièrement responsive et optimisé pour tous les types d\'appareils (mobile, tablette, ordinateur).'
                    ],
                    [
                        'question' => 'Comment créer un compte ?',
                        'answer' => 'Cliquez sur "Se connecter" puis "Créer un compte". L\'inscription est gratuite et permet d\'accéder à des fonctionnalités supplémentaires.'
                    ]
                ]
            ]
        ];

        return $this->render('frontend/pages/faq.html.twig', [
            'faqs' => $faqs
        ]);
    }

    public function terms(): Response
    {
        return $this->render('frontend/pages/terms.html.twig');
    }

    public function privacy(): Response
    {
        return $this->render('frontend/pages/privacy.html.twig');
    }

    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $type = $request->query->get('type', 'all'); // all, articles, podcasts, archives, teachers
        
        $results = [
            'articles' => [],
            'podcasts' => [],
            'archives' => [],
            'teachers' => []
        ];

        if (strlen($query) >= 3) {
            // Search in articles
            if ($type === 'all' || $type === 'articles') {
                $results['articles'] = $this->articleRepository->createQueryBuilder('a')
                    ->where('a.isPublished = :published')
                    ->andWhere('a.title LIKE :query OR a.content LIKE :query')
                    ->setParameter('published', true)
                    ->setParameter('query', '%' . $query . '%')
                    ->orderBy('a.publishedAt', 'DESC')
                    ->setMaxResults(10)
                    ->getQuery()
                    ->getResult();
            }
        }

        return $this->render('frontend/pages/search.html.twig', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'hasResults' => !empty(array_filter($results))
        ]);
    }

    public function sitemapHtml(): Response
    {
        // Get recent articles for sitemap
        $recentArticles = $this->articleRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            50
        );

        $categories = $this->categoryRepository->findBy(['isActive' => true]);

        return $this->render('frontend/pages/sitemap.html.twig', [
            'recentArticles' => $recentArticles,
            'categories' => $categories
        ]);
    }
}
