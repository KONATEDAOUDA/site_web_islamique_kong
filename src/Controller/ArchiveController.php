<?php

namespace App\Controller;

use App\Entity\Archive;
use App\Repository\ArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/archives')]
class ArchiveController extends AbstractController
{
    #[Route('/', name: 'app_archive_index')]
    public function index(ArchiveRepository $archiveRepository): Response
    {
        $articleArchives = $archiveRepository->findBy(['type' => 'article', 'isPublished' => true], ['createdAt' => 'DESC']);
        $audioArchives = $archiveRepository->findBy(['type' => 'audio', 'isPublished' => true], ['createdAt' => 'DESC']);
        $videoArchives = $archiveRepository->findBy(['type' => 'video', 'isPublished' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/archive/index.html.twig', [
            'articleArchives' => $articleArchives,
            'audioArchives' => $audioArchives,
            'videoArchives' => $videoArchives,
        ]);
    }

    #[Route('/type/{type}', name: 'app_archive_type')]
    public function showByType(string $type, ArchiveRepository $archiveRepository): Response
    {
        if (!in_array($type, ['article', 'audio', 'video'])) {
            throw $this->createNotFoundException('Ce type d\'archive n\'existe pas');
        }

        $archives = $archiveRepository->findBy(['type' => $type, 'isPublished' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/archive/type.html.twig', [
            'archives' => $archives,
            'type' => $type,
        ]);
    }

    #[Route('/periode/{year}', name: 'app_archive_period', requirements: ['year' => '\d+'])]
    public function showByPeriod(int $year, ArchiveRepository $archiveRepository): Response
    {
        $archives = $archiveRepository->findBy(['periodYear' => $year, 'isPublished' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/archive/period.html.twig', [
            'archives' => $archives,
            'year' => $year,
        ]);
    }

    #[Route('/search', name: 'app_archive_search', methods: ['GET'])]
    public function search(Request $request, ArchiveRepository $archiveRepository): Response
    {
        $query = $request->query->get('q');
        $type = $request->query->get('type');
        $year = $request->query->get('year');
        
        $criteria = ['isPublished' => true];
        
        if ($type && in_array($type, ['article', 'audio', 'video'])) {
            $criteria['type'] = $type;
        }
        
        if ($year) {
            $criteria['periodYear'] = $year;
        }
        
        // Pour une recherche plus avancée, vous pourriez utiliser un repository custom
        // Ici on utilise une recherche simple
        $results = $archiveRepository->findBy($criteria, ['createdAt' => 'DESC']);
        
        // Si une recherche par mot-clé, filtrer manuellement
        if ($query) {
            $results = array_filter($results, function($archive) use ($query) {
                return stripos($archive->getTitle(), $query) !== false 
                    || stripos($archive->getDescription(), $query) !== false;
            });
        }

        return $this->render('frontend/archive/search.html.twig', [
            'archives' => $results,
            'query' => $query,
            'type' => $type,
            'year' => $year,
        ]);
    }

    #[Route('/{slug}', name: 'app_archive_show')]
    public function show(string $slug, ArchiveRepository $archiveRepository): Response
    {
        $archive = $archiveRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$archive) {
            throw $this->createNotFoundException('Cette archive n\'existe pas ou n\'est pas publiée');
        }

        // Archives recommandées (même type)
        $relatedArchives = $archiveRepository->findBy(
            ['type' => $archive->getType(), 'isPublished' => true],
            ['createdAt' => 'DESC'],
            3
        );

        return $this->render('frontend/archive/show.html.twig', [
            'archive' => $archive,
            'relatedArchives' => $relatedArchives,
        ]);
    }

    #[Route('/{id}/favorite', name: 'app_archive_favorite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addToFavorites(Archive $archive, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->getFavoriteArchives()->contains($archive)) {
            $user->removeFavoriteArchive($archive);
            $message = 'L\'archive a été retirée de vos favoris';
        } else {
            $user->addFavoriteArchive($archive);
            $message = 'L\'archive a été ajoutée à vos favoris';
        }

        $entityManager->flush();

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_archive_show', ['slug' => $archive->getSlug()]);
    }
}