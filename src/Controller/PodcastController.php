<?php

namespace App\Controller;

use App\Repository\MaitreIslamiqueRepository;
use App\Repository\PodcastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;

class PodcastController extends AbstractController
{
    public function __construct(
        private PodcastRepository $podcastRepository,
        private MaitreIslamiqueRepository $teacherRepository
    ) {}

    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->podcastRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.publishedAt', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC');

        // Search filter
        $search = $request->query->get('q');
        if ($search) {
            $queryBuilder->andWhere('p.title LIKE :search OR p.description LIKE :search OR p.tags LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        // Type filter
        $type = $request->query->get('type');
        if ($type && in_array($type, ['audio', 'video'])) {
            $queryBuilder->andWhere('p.type = :type')
                         ->setParameter('type', $type);
        }

        // Teacher filter
        $teacherSlug = $request->query->get('teacher');
        if ($teacherSlug) {
            $teacher = $this->teacherRepository->findOneBy(['slug' => $teacherSlug]);
            if ($teacher) {
                $queryBuilder->andWhere('p.author = :teacher')
                             ->setParameter('teacher', $teacher);
            }
        }

        // Language filter
        $language = $request->query->get('language');
        if ($language && in_array($language, ['fr', 'ar', 'en'])) {
            $queryBuilder->andWhere('p.language = :language')
                         ->setParameter('language', $language);
        }

        $podcasts = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        // Separate audio and video for display
        $audioPodcasts = [];
        $videoPodcasts = [];
        
        foreach ($podcasts as $podcast) {
            if ($podcast->getType() === 'audio') {
                $audioPodcasts[] = $podcast;
            } else {
                $videoPodcasts[] = $podcast;
            }
        }

        // Get teachers for filter
        $teachers = $this->teacherRepository->findBy(['isPublished' => true], ['id' => 'ASC']);

        return $this->render('frontend/podcast/index.html.twig', [
            'podcasts' => $podcasts,
            'audioPodcasts' => $audioPodcasts,
            'videoPodcasts' => $videoPodcasts,
            'teachers' => $teachers,
            'currentSearch' => $search,
            'currentType' => $type,
            'currentTeacher' => $teacherSlug,
            'currentLanguage' => $language,
        ]);
    }

    public function show(string $slug): Response
    {
        $podcast = $this->podcastRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$podcast) {
            throw $this->createNotFoundException('Podcast non trouvé');
        }

        // Increment view count if method exists
        if (method_exists($podcast, 'incrementViewCount')) {
            $podcast->incrementViewCount();
            $this->podcastRepository->save($podcast, true);
        }

        // Get related podcasts
        $relatedPodcasts = [];

        // Get other podcasts from same author
        $authorPodcasts = [];
        if ($podcast->getAuthor()) {
            $authorPodcasts = $this->podcastRepository->findBy([
                'author' => $podcast->getAuthor(),
                'isPublished' => true
            ], ['publishedAt' => 'DESC'], 5);
            
            // Remove current podcast from the list
            $authorPodcasts = array_filter($authorPodcasts, function($p) use ($podcast) {
                return $p->getId() !== $podcast->getId();
            });
        }

        return $this->render('frontend/podcast/show_podcast.html.twig', [
            'podcast' => $podcast,
            'relatedPodcasts' => $relatedPodcasts,
            'authorPodcasts' => array_slice($authorPodcasts, 0, 4),
        ]);
    }

    public function incrementDownload(int $id): JsonResponse
    {
        $podcast = $this->podcastRepository->find($id);
        
        if (!$podcast || !$podcast->isPublished()) {
            return new JsonResponse(['success' => false], 404);
        }

        if (method_exists($podcast, 'incrementDownloadCount')) {
            $podcast->incrementDownloadCount();
            $this->podcastRepository->save($podcast, true);
        }

        return new JsonResponse(['success' => true]);
    }

    public function byType(string $type, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array($type, ['audio', 'video'])) {
            throw $this->createNotFoundException('Type de podcast non valide');
        }

        $queryBuilder = $this->podcastRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->andWhere('p.type = :type')
            ->setParameter('published', true)
            ->setParameter('type', $type)
            ->orderBy('p.publishedAt', 'DESC');

        $podcasts = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        $typeLabel = $type === 'audio' ? 'Podcasts Audio' : 'Podcasts Vidéo';

        return $this->render('frontend/podcast/index.html.twig', [
            'podcasts' => $podcasts,
            'currentType' => $type,
            'pageTitle' => $typeLabel,
            'audioPodcasts' => $type === 'audio' ? iterator_to_array($podcasts) : [],
            'videoPodcasts' => $type === 'video' ? iterator_to_array($podcasts) : [],
        ]);
    }

    public function byTeacher(string $teacherSlug, Request $request, PaginatorInterface $paginator): Response
    {
        $teacher = $this->teacherRepository->findOneBy(['slug' => $teacherSlug, 'isPublished' => true]);
        
        if (!$teacher) {
            throw $this->createNotFoundException('Maître non trouvé');
        }

        $queryBuilder = $this->podcastRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->andWhere('p.author = :teacher')
            ->setParameter('published', true)
            ->setParameter('teacher', $teacher)
            ->orderBy('p.publishedAt', 'DESC');

        $podcasts = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        $teacherName = $teacher->getNomComplet();

        return $this->render('frontend/podcast/index.html.twig', [
            'podcasts' => $podcasts,
            'currentTeacher' => $teacherSlug,
            'pageTitle' => 'Podcasts de ' . $teacherName,
            'teacher' => $teacher,
        ]);
    }

    public function byLanguage(string $language, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array($language, ['fr', 'ar', 'en'])) {
            throw $this->createNotFoundException('Langue non valide');
        }

        $queryBuilder = $this->podcastRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->andWhere('p.language = :language')
            ->setParameter('published', true)
            ->setParameter('language', $language)
            ->orderBy('p.publishedAt', 'DESC');

        $podcasts = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        $languageLabels = [
            'fr' => 'Français',
            'ar' => 'العربية',
            'en' => 'English'
        ];

        return $this->render('frontend/podcast/index.html.twig', [
            'podcasts' => $podcasts,
            'currentLanguage' => $language,
            'pageTitle' => 'Podcasts en ' . $languageLabels[$language],
        ]);
    }

    public function searchAjax(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 3) {
            return new JsonResponse(['results' => []]);
        }

        $podcasts = $this->podcastRepository->createQueryBuilder('p')
            ->where('p.isPublished = :published')
            ->andWhere('p.title LIKE :query OR p.description LIKE :query')
            ->setParameter('published', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.publishedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($podcasts as $podcast) {
            $authorName = null;
            if ($podcast->getAuthor()) {
                $authorName = $podcast->getAuthor()->getNomComplet();
            }

            $results[] = [
                'id' => $podcast->getId(),
                'title' => $podcast->getTitle(),
                'slug' => $podcast->getSlug(),
                'type' => $podcast->getType(),
                'author' => $authorName,
                'duration' => $podcast->getDuration(),
                'thumbnail' => $podcast->getThumbnail() ? '/assets/uploads/podcasts/thumbnails/' . $podcast->getThumbnail() : null,
            ];
        }

        return new JsonResponse(['results' => $results]);
    }
}
