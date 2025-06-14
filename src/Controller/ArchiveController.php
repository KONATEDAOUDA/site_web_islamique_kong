<?php

namespace App\Controller;

use App\Repository\ArchiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Knp\Component\Pager\PaginatorInterface;

class ArchiveController extends AbstractController
{
    public function __construct(
        private ArchiveRepository $archiveRepository
    ) {}

    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->archiveRepository->createQueryBuilder('a')
            ->where('a.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('a.createdAt', 'DESC');

        // Search filter
        $search = $request->query->get('q');
        if ($search) {
            $queryBuilder->andWhere('a.title LIKE :search OR a.description LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        // Type filter
        $type = $request->query->get('type');
        if ($type && in_array($type, ['manuscript', 'document', 'photo', 'audio', 'video'])) {
            $queryBuilder->andWhere('a.type = :type')
                         ->setParameter('type', $type);
        }

        // Collection filter
        $collection = $request->query->get('collection');
        if ($collection) {
            $queryBuilder->andWhere('a.collection = :collection')
                         ->setParameter('collection', $collection);
        }

        $archives = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            15
        );

        // Get archive statistics
        $archiveStats = $this->getArchiveStatistics();

        return $this->render('frontend/archive/index.html.twig', [
            'archives' => $archives,
            'archiveStats' => $archiveStats,
            'currentSearch' => $search,
            'currentType' => $type,
            'currentCollection' => $collection,
        ]);
    }

    public function show(string $slug): Response
    {
        $archive = $this->archiveRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$archive) {
            throw $this->createNotFoundException('Archive non trouvée');
        }

        // Increment view count if method exists
        if (method_exists($archive, 'incrementViewCount')) {
            $archive->incrementViewCount();
            $this->archiveRepository->save($archive, true);
        }

        // Get related archives
        $relatedArchives = [];

        return $this->render('frontend/archive/show_archive.html.twig', [
            'archive' => $archive,
            'relatedArchives' => $relatedArchives,
        ]);
    }

    public function download(string $slug): Response
    {
        $archive = $this->archiveRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);

        if (!$archive || !method_exists($archive, 'getFilePath') || !$archive->getFilePath()) {
            throw $this->createNotFoundException('Fichier non disponible au téléchargement');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/assets/uploads/archives/' . $archive->getFilePath();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier non trouvé');
        }

        // Increment download count if method exists
        if (method_exists($archive, 'incrementDownloadCount')) {
            $archive->incrementDownloadCount();
            $this->archiveRepository->save($archive, true);
        }

        $response = new BinaryFileResponse($filePath);
        $filename = method_exists($archive, 'getOriginalFilename') && $archive->getOriginalFilename() ? 
            $archive->getOriginalFilename() : 
            $archive->getTitle() . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
            
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }

    public function byType(string $type, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array($type, ['manuscript', 'document', 'photo', 'audio', 'video'])) {
            throw $this->createNotFoundException('Type d\'archive non valide');
        }

        $queryBuilder = $this->archiveRepository->createQueryBuilder('a')
            ->where('a.isPublished = :published')
            ->andWhere('a.type = :type')
            ->setParameter('published', true)
            ->setParameter('type', $type)
            ->orderBy('a.createdAt', 'DESC');

        $archives = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            15
        );

        $typeLabels = [
            'manuscript' => 'Manuscrits',
            'document' => 'Documents',
            'photo' => 'Photographies',
            'audio' => 'Enregistrements Audio',
            'video' => 'Vidéos'
        ];

        return $this->render('frontend/archive/index.html.twig', [
            'archives' => $archives,
            'currentType' => $type,
            'pageTitle' => $typeLabels[$type],
            'archiveStats' => $this->getArchiveStatistics(),
        ]);
    }

    public function byCollection(string $collection, Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $this->archiveRepository->createQueryBuilder('a')
            ->where('a.isPublished = :published')
            ->andWhere('a.collection = :collection')
            ->setParameter('published', true)
            ->setParameter('collection', $collection)
            ->orderBy('a.createdAt', 'DESC');

        $archives = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            15
        );

        $collectionLabels = [
            'architecture' => 'Architecture Islamique',
            'manuscripts' => 'Manuscrits Anciens',
            'scholars' => 'Portraits de Maîtres',
            'audio' => 'Enregistrements Historiques'
        ];

        return $this->render('frontend/archive/index.html.twig', [
            'archives' => $archives,
            'currentCollection' => $collection,
            'pageTitle' => $collectionLabels[$collection] ?? 'Collection ' . ucfirst($collection),
            'archiveStats' => $this->getArchiveStatistics(),
        ]);
    }

    private function getArchiveStatistics(): array
    {
        $totalDocuments = $this->archiveRepository->count(['isPublished' => true]);
        $manuscripts = $this->archiveRepository->count(['isPublished' => true, 'type' => 'manuscript']);
        $audioRecordings = $this->archiveRepository->count(['isPublished' => true, 'type' => 'audio']);
        $photos = $this->archiveRepository->count(['isPublished' => true, 'type' => 'photo']);

        return [
            'totalDocuments' => $totalDocuments,
            'manuscripts' => $manuscripts,
            'audioRecordings' => $audioRecordings,
            'photos' => $photos,
        ];
    }
}
