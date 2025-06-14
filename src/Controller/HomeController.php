<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\PodcastRepository;
use App\Repository\ArchiveRepository;
use App\Repository\CategoryRepository;
use App\Repository\MaitreIslamiqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private PodcastRepository $podcastRepository,
        private MaitreIslamiqueRepository $teacherRepository,
        private ArchiveRepository $archiveRepository,
        private CategoryRepository $categoryRepository
    ) {}

    public function index(): Response
    {
        // Récupérer les derniers articles publiés (limit 6)
        $latestArticles = $this->articleRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            6
        );

        // Récupérer les derniers podcasts (limit 3)
        $latestPodcasts = $this->podcastRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            3
        );

        // Récupérer les maîtres actifs (limit 8)
        $teachers = $this->teacherRepository->findBy(
            ['isPublished' => true], 
            ['id' => 'ASC'], 
            8
        );

        // Récupérer quelques archives pertinentes (limit 6)
        $relevantArchives = $this->archiveRepository->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC'],
            6
        );

        // Récupérer les catégories pour la navigation - CORRECTION: isActive au lieu de 'valid'
        $categories = $this->categoryRepository->findBy(
            ['valid' => true],
            ['id' => 'ASC']
        );

        return $this->render('frontend/home/index.html.twig', [
            'latestArticles' => $latestArticles,
            'latestPodcasts' => $latestPodcasts,
            'teachers' => $teachers,
            'relevantArchives' => $relevantArchives,
            'categories' => $categories,
        ]);
    }

    public function about(): Response
    {
        return $this->render('frontend/pages/about.html.twig');
    }

    public function contact(): Response
    {
        return $this->render('frontend/pages/contact.html.twig');
    }
}
