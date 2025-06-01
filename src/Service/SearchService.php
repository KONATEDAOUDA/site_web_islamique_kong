<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
use App\Entity\Podcast;
use App\Entity\Archive;
use App\Entity\Enseignement;

class SearchService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function searchAll(string $query, int $limit = 20): array
    {
        $results = [
            'articles' => $this->searchArticles($query, $limit),
            'podcasts' => $this->searchPodcasts($query, $limit),
            'archives' => $this->searchArchives($query, $limit),
            'enseignements' => $this->searchEnseignements($query, $limit),
        ];

        return $results;
    }

    public function searchArticles(string $query, int $limit = 10): array
    {
        return $this->entityManager->getRepository(Article::class)
            ->createQueryBuilder('a')
            ->where('a.title LIKE :query OR a.content LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchPodcasts(string $query, int $limit = 10): array
    {
        return $this->entityManager->getRepository(Podcast::class)
            ->createQueryBuilder('p')
            ->where('p.title LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchArchives(string $query, int $limit = 10): array
    {
        return $this->entityManager->getRepository(Archive::class)
            ->createQueryBuilder('ar')
            ->where('ar.title LIKE :query OR ar.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('ar.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchEnseignements(string $query, int $limit = 10): array
    {
        return $this->entityManager->getRepository(Enseignement::class)
            ->createQueryBuilder('e')
            ->where('e.title LIKE :query OR e.content LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByCategory(string $query, string $category, int $limit = 20): array
    {
        return match($category) {
            'articles' => $this->searchArticles($query, $limit),
            'podcasts' => $this->searchPodcasts($query, $limit),
            'archives' => $this->searchArchives($query, $limit),
            'enseignements' => $this->searchEnseignements($query, $limit),
            default => []
        };
    }

    public function getPopularContent(int $limit = 10): array
    {
        // Retourne du contenu populaire (vous pouvez ajouter une logique basée sur les vues, likes, etc.)
        return [
            'articles' => $this->entityManager->getRepository(Article::class)
                ->findBy([], ['createdAt' => 'DESC'], $limit),
            'podcasts' => $this->entityManager->getRepository(Podcast::class)
                ->findBy([], ['createdAt' => 'DESC'], $limit),
        ];
    }
}