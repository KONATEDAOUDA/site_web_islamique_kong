<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\MaitreIslamiqueRepository;
use App\Repository\PodcastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class TeacherController extends AbstractController
{
    public function __construct(
        private MaitreIslamiqueRepository $teacherRepository,
        private ArticleRepository $articleRepository,
        private PodcastRepository $podcastRepository
    ) {}

    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->teacherRepository->createQueryBuilder('t')
            ->where('t.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('t.id', 'ASC');

        // Filter by specialty if provided
        $specialty = $request->query->get('specialty');
        if ($specialty) {
            $queryBuilder->andWhere('t.specialite LIKE :specialty')
                         ->setParameter('specialty', '%' . $specialty . '%');
        }

        // Filter by era if provided
        $era = $request->query->get('era');
        if ($era && in_array($era, ['historical', 'contemporary'])) {
            if ($era === 'historical') {
                $queryBuilder->andWhere('t.anneeDeces IS NOT NULL OR t.anneeNaissance < :currentYear - 100')
                             ->setParameter('currentYear', date('Y'));
            } else {
                $queryBuilder->andWhere('t.anneeDeces IS NULL AND (t.anneeNaissance > :currentYear - 100 OR t.anneeNaissance IS NULL)')
                             ->setParameter('currentYear', date('Y'));
            }
        }

        $teachers = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('frontend/teacher/index.html.twig', [
            'teachers' => $teachers,
            'currentSpecialty' => $specialty,
            'currentEra' => $era,
        ]);
    }

    public function show(string $slug): Response
    {
        $teacher = $this->teacherRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$teacher) {
            throw $this->createNotFoundException('Maître non trouvé');
        }

        // Get related teachers (same specialty, excluding current teacher)
        $relatedTeachers = $this->teacherRepository->findBy([
            'specialite' => $teacher->getSpecialite(),
            'isPublished' => true
        ], ['id' => 'ASC'], 4);

        // Remove current teacher from related teachers
        $relatedTeachers = array_filter($relatedTeachers, function($t) use ($teacher) {
            return $t->getId() !== $teacher->getId();
        });

        // Get articles related to this teacher
        $relatedArticles = [];

        // Get teacher's podcasts
        $teacherPodcasts = $this->podcastRepository->findBy([
            'author' => $teacher,
            'isPublished' => true
        ], ['publishedAt' => 'DESC'], 5);

        return $this->render('frontend/teacher/show_teacher.html.twig', [
            'teacher' => $teacher,
            'relatedTeachers' => array_slice($relatedTeachers, 0, 3),
            'relatedArticles' => $relatedArticles,
            'teacherPodcasts' => $teacherPodcasts,
        ]);
    }

    public function bySpecialty(string $specialty, Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->teacherRepository->createQueryBuilder('t')
            ->where('t.isPublished = :published')
            ->andWhere('t.specialite LIKE :specialty')
            ->setParameter('published', true)
            ->setParameter('specialty', '%' . $specialty . '%')
            ->orderBy('t.id', 'ASC');

        $teachers = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('frontend/teacher/index.html.twig', [
            'teachers' => $teachers,
            'currentSpecialty' => $specialty,
            'pageTitle' => 'Maîtres spécialisés en ' . $specialty,
        ]);
    }

    public function byEra(string $era, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array($era, ['historical', 'contemporary'])) {
            throw $this->createNotFoundException('Époque non valide');
        }

        $queryBuilder = $this->teacherRepository->createQueryBuilder('t')
            ->where('t.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('t.id', 'ASC');

        // Logique basée sur les années de naissance/décès
        if ($era === 'historical') {
            $queryBuilder->andWhere('t.anneeDeces IS NOT NULL OR t.anneeNaissance < :currentYear - 100')
                         ->setParameter('currentYear', date('Y'));
        } else {
            $queryBuilder->andWhere('t.anneeDeces IS NULL AND (t.anneeNaissance > :currentYear - 100 OR t.anneeNaissance IS NULL)')
                         ->setParameter('currentYear', date('Y'));
        }

        $teachers = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        $eraLabel = $era === 'historical' ? 'Historiques' : 'Contemporains';

        return $this->render('frontend/teacher/index.html.twig', [
            'teachers' => $teachers,
            'currentEra' => $era,
            'pageTitle' => 'Maîtres ' . $eraLabel,
        ]);
    }
}
