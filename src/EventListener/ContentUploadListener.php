<?php

namespace App\EventListener;

use App\Entity\Article;
use App\Entity\Podcast;
use App\Entity\Archive;
use App\Entity\Enseignement;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

class ContentUploadListener
{
    private SluggerInterface $slugger;
    private LoggerInterface $logger;

    public function __construct(SluggerInterface $slugger, LoggerInterface $logger)
    {
        $this->slugger = $slugger;
        $this->logger = $logger;
    }

    /**
     * Appelé avant la persistance d'une nouvelle entité
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        try {
            if ($this->isSupportedEntity($entity)) {
                $this->processEntity($entity, 'create');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in ContentUploadListener::prePersist', [
                'entity' => get_class($entity),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Relancer l'exception pour éviter une sauvegarde partielle
            throw $e;
        }
    }

    /**
     * Appelé avant la mise à jour d'une entité
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        
        try {
            if ($this->isSupportedEntity($entity)) {
                $this->processEntity($entity, 'update', $args);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in ContentUploadListener::preUpdate', [
                'entity' => get_class($entity),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Vérifie si l'entité est supportée par ce listener
     */
    private function isSupportedEntity($entity): bool
    {
        return $entity instanceof Article || 
               $entity instanceof Podcast || 
               $entity instanceof Archive || 
               $entity instanceof Enseignement;
    }

    /**
     * Traite l'entité selon son type et l'opération
     */
    private function processEntity($entity, string $operation, PreUpdateEventArgs $args = null): void
    {
        $entityClass = get_class($entity);
        
        // Log pour debugging
        $this->logger->info("Processing entity in ContentUploadListener", [
            'entity' => $entityClass,
            'operation' => $operation
        ]);

        // Gestion du slug
        if ($operation === 'create' || ($args && $this->shouldUpdateSlug($entity, $args))) {
            $this->updateSlug($entity);
        }
        
        // Gestion des dates
        if ($operation === 'create') {
            $this->setCreationDates($entity);
        } else {
            $this->setUpdateDate($entity);
        }

        // Gestion spécifique par type d'entité
        match($entityClass) {
            Article::class => $this->processArticle($entity, $operation),
            Podcast::class => $this->processPodcast($entity, $operation),
            Archive::class => $this->processArchive($entity, $operation),
            Enseignement::class => $this->processEnseignement($entity, $operation),
            default => null
        };
    }

    /**
     * Vérifie si le slug doit être mis à jour
     */
    private function shouldUpdateSlug($entity, PreUpdateEventArgs $args): bool
    {
        // Pour Article, Podcast, Archive
        if ($args->hasChangedField('title')) {
            return true;
        }
        
        // Pour Enseignement
        if ($entity instanceof Enseignement && $args->hasChangedField('titre')) {
            return true;
        }
        
        return false;
    }

    /**
     * Met à jour le slug de l'entité
     */
    private function updateSlug($entity): void
    {
        try {
            $title = $this->getEntityTitle($entity);
            
            if (!$title || !method_exists($entity, 'setSlug')) {
                return;
            }

            // Ne générer un nouveau slug que s'il n'y en a pas
            if (!method_exists($entity, 'getSlug') || !$entity->getSlug()) {
                $slug = $this->slugger->slug($title)->lower();
                $entity->setSlug($slug);
                
                $this->logger->info("Generated slug", [
                    'entity' => get_class($entity),
                    'title' => $title,
                    'slug' => $slug
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error generating slug", [
                'entity' => get_class($entity),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère le titre de l'entité selon son type
     */
    private function getEntityTitle($entity): ?string
    {
        if ($entity instanceof Enseignement && method_exists($entity, 'getTitre')) {
            return $entity->getTitre();
        }
        
        if (method_exists($entity, 'getTitle')) {
            return $entity->getTitle();
        }
        
        return null;
    }

    /**
     * Définit les dates de création
     */
    private function setCreationDates($entity): void
    {
        $now = new \DateTime();
        
        if (method_exists($entity, 'setCreatedAt') && 
            (!method_exists($entity, 'getCreatedAt') || !$entity->getCreatedAt())) {
            $entity->setCreatedAt($now);
        }
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }
    }

    /**
     * Met à jour la date de modification
     */
    private function setUpdateDate($entity): void
    {
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * Traitement spécifique pour les articles
     */
    private function processArticle(Article $article, string $operation): void
    {
        try {
            if ($operation === 'create') {
                $this->calculateReadingTime($article);
                $this->generateExcerptIfEmpty($article);
            }
            
            $this->setPublishedDateIfNeeded($article);
        } catch (\Exception $e) {
            $this->logger->error("Error processing article", [
                'article_id' => method_exists($article, 'getId') ? $article->getId() : 'new',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Traitement spécifique pour les podcasts
     */
    private function processPodcast(Podcast $podcast, string $operation): void
    {
        try {
            $this->setPublishedDateIfNeeded($podcast);
            
            // Calculer la durée si possible
            if (method_exists($podcast, 'getDuration') && 
                method_exists($podcast, 'setCalculatedDuration') &&
                $podcast->getDuration()) {
                $podcast->setCalculatedDuration($podcast->getDuration());
            }
        } catch (\Exception $e) {
            $this->logger->error("Error processing podcast", [
                'podcast_id' => method_exists($podcast, 'getId') ? $podcast->getId() : 'new',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Traitement spécifique pour les archives
     */
    private function processArchive(Archive $archive, string $operation): void
    {
        try {
            $this->setPublishedDateIfNeeded($archive);
        } catch (\Exception $e) {
            $this->logger->error("Error processing archive", [
                'archive_id' => method_exists($archive, 'getId') ? $archive->getId() : 'new',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Traitement spécifique pour les enseignements
     */
    private function processEnseignement(Enseignement $enseignement, string $operation): void
    {
        try {
            $this->setPublishedDateIfNeeded($enseignement);
        } catch (\Exception $e) {
            $this->logger->error("Error processing enseignement", [
                'enseignement_id' => method_exists($enseignement, 'getId') ? $enseignement->getId() : 'new',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calcule le temps de lecture pour les articles
     */
    private function calculateReadingTime(Article $article): void
    {
        if (!method_exists($article, 'setReadingTime') || 
            !method_exists($article, 'getContent') || 
            !$article->getContent()) {
            return;
        }

        $content = strip_tags($article->getContent());
        $wordCount = str_word_count($content);
        
        // Vitesse de lecture moyenne : 200 mots par minute
        $readingTime = max(1, ceil($wordCount / 200));
        
        $article->setReadingTime($readingTime);
    }

    /**
     * Génère automatiquement un extrait si l'article n'en a pas
     */
    private function generateExcerptIfEmpty(Article $article): void
    {
        if (!method_exists($article, 'getExcerpt') || 
            !method_exists($article, 'setExcerpt') || 
            !method_exists($article, 'getContent') ||
            $article->getExcerpt()) {
            return;
        }

        $content = strip_tags($article->getContent());
        if (strlen($content) > 200) {
            $excerpt = substr($content, 0, 200);
            
            // Couper au dernier espace pour éviter de couper un mot
            $lastSpace = strrpos($excerpt, ' ');
            if ($lastSpace !== false) {
                $excerpt = substr($excerpt, 0, $lastSpace);
            }
            
            $article->setExcerpt($excerpt . '...');
        }
    }

    /**
     * Met automatiquement la date de publication lors de la publication
     */
    private function setPublishedDateIfNeeded($entity): void
    {
        if (!method_exists($entity, 'isIsPublished') || 
            !method_exists($entity, 'setPublishedAt') ||
            !method_exists($entity, 'getPublishedAt')) {
            return;
        }

        // Si l'entité vient d'être publiée et n'a pas encore de date de publication
        if ($entity->isIsPublished() && !$entity->getPublishedAt()) {
            $entity->setPublishedAt(new \DateTime());
        }
    }
}
