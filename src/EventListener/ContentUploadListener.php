<?php

namespace App\EventListener;

use App\Entity\Article;
use App\Entity\Podcast;
use App\Entity\Archive;
use App\Entity\Enseignement;
use App\Service\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ContentUploadListener
{
    private FileUploader $fileUploader;
    private SluggerInterface $slugger;

    public function __construct(FileUploader $fileUploader, SluggerInterface $slugger)
    {
        $this->fileUploader = $fileUploader;
        $this->slugger = $slugger;
    }

    /**
     * Appelé avant la persistance d'une nouvelle entité
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Article) {
            $this->handleArticleUpload($entity);
            $this->generateSlug($entity);
        } elseif ($entity instanceof Podcast) {
            $this->handlePodcastUpload($entity);
            $this->generateSlug($entity);
        } elseif ($entity instanceof Archive) {
            $this->handleArchiveUpload($entity);
            $this->generateSlug($entity);
        } elseif ($entity instanceof Enseignement) {
            $this->handleEnseignementUpload($entity);
            $this->generateSlug($entity);
        }
    }

    /**
     * Appelé avant la mise à jour d'une entité
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Article) {
            $this->handleArticleUpload($entity);
            if ($args->hasChangedField('title')) {
                $this->generateSlug($entity);
            }
        } elseif ($entity instanceof Podcast) {
            $this->handlePodcastUpload($entity);
            if ($args->hasChangedField('title')) {
                $this->generateSlug($entity);
            }
        } elseif ($entity instanceof Archive) {
            $this->handleArchiveUpload($entity);
            if ($args->hasChangedField('title')) {
                $this->generateSlug($entity);
            }
        } elseif ($entity instanceof Enseignement) {
            $this->handleEnseignementUpload($entity);
            if ($args->hasChangedField('title')) {
                $this->generateSlug($entity);
            }
        }
    }

    /**
     * Gestion des uploads pour les articles
     */
    private function handleArticleUpload(Article $article): void
    {
        $featuredImageFile = $article->getFeaturedImageFile();
        
        if ($featuredImageFile instanceof UploadedFile) {
            try {
                // Upload avec création de miniatures
                $images = $this->fileUploader->uploadImage($featuredImageFile, 'articles');
                $article->setFeaturedImage($images['original']);
                
                // Optionnel : stocker les chemins des miniatures
                if (method_exists($article, 'setFeaturedImageThumbnail')) {
                    $article->setFeaturedImageThumbnail($images['thumbnail'] ?? null);
                }
                
                // Réinitialiser le fichier temporaire
                $article->setFeaturedImageFile(null);
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la persistance
                error_log('Erreur upload image article: ' . $e->getMessage());
            }
        }
    }

    /**
     * Gestion des uploads pour les podcasts
     */
    private function handlePodcastUpload(Podcast $podcast): void
    {
        // Upload du fichier audio/vidéo
        $mediaFile = $podcast->getAudioFile();
        if ($mediaFile instanceof UploadedFile) {
            try {
                $fileName = $this->fileUploader->uploadMedia($mediaFile, 'podcasts');
                $podcast->setAudioPath($fileName);
                $podcast->setAudioFile(null);
                
                // Calculer automatiquement la durée si possible
                $this->calculateMediaDuration($podcast, $fileName);
            } catch (\Exception $e) {
                error_log('Erreur upload média podcast: ' . $e->getMessage());
            }
        }

        // Upload de l'image de couverture
        $thumbnailFile = $podcast->getThumbnailFile();
        if ($thumbnailFile instanceof UploadedFile) {
            try {
                $images = $this->fileUploader->uploadImage($thumbnailFile, 'podcasts');
                $podcast->setThumbnail($images['original']);
                $podcast->setThumbnailFile(null);
            } catch (\Exception $e) {
                error_log('Erreur upload thumbnail podcast: ' . $e->getMessage());
            }
        }
    }

    /**
     * Gestion des uploads pour les archives
     */
    private function handleArchiveUpload(Archive $archive): void
    {
        // Upload du fichier d'archive
        $archiveFile = $archive->getArchiveFile();
        if ($archiveFile instanceof UploadedFile) {
            try {
                if ($this->isImageFile($archiveFile)) {
                    // Si c'est une image, créer des miniatures
                    $images = $this->fileUploader->uploadImage($archiveFile, 'archives');
                    $archive->setFilePath($images['original']);
                } else {
                    // Sinon, upload normal
                    $fileName = $this->fileUploader->uploadDocument($archiveFile, 'archives');
                    $archive->setFilePath($fileName);
                }
                
                $archive->setArchiveFile(null);
                
                // Stocker les métadonnées du fichier
                $this->setFileMetadata($archive, $archiveFile);
            } catch (\Exception $e) {
                error_log('Erreur upload fichier archive: ' . $e->getMessage());
            }
        }

        // Upload de l'image de prévisualisation
        $thumbnailFile = $archive->getThumbnailFile();
        if ($thumbnailFile instanceof UploadedFile) {
            try {
                $images = $this->fileUploader->uploadImage($thumbnailFile, 'archives');
                $archive->setThumbnail($images['original']);
                $archive->setThumbnailFile(null);
            } catch (\Exception $e) {
                error_log('Erreur upload thumbnail archive: ' . $e->getMessage());
            }
        }
    }

    /**
     * Gestion des uploads pour les enseignements
     */
    private function handleEnseignementUpload(Enseignement $enseignement): void
    {
        // Upload du document de support
        $supportFile = $enseignement->getSupportFile();
        if ($supportFile instanceof UploadedFile) {
            try {
                $fileName = $this->fileUploader->uploadDocument($supportFile, 'enseignements');
                $enseignement->setSupportPath($fileName);
                $enseignement->setSupportFile(null);
            } catch (\Exception $e) {
                error_log('Erreur upload support enseignement: ' . $e->getMessage());
            }
        }

        // Upload du fichier audio
        $audioFile = $enseignement->getAudioFile();
        if ($audioFile instanceof UploadedFile) {
            try {
                $fileName = $this->fileUploader->uploadMedia($audioFile, 'enseignements');
                $enseignement->setAudioPath($fileName);
                $enseignement->setAudioFile(null);
            } catch (\Exception $e) {
                error_log('Erreur upload audio enseignement: ' . $e->getMessage());
            }
        }
    }

    /**
     * Génère un slug unique pour l'entité
     */
    private function generateSlug($entity): void
    {
        if (!method_exists($entity, 'getTitle') || !method_exists($entity, 'setSlug')) {
            return;
        }

        $title = $entity->getTitle();
        if (!$title) {
            return;
        }

        // Créer le slug de base
        $baseSlug = $this->slugger->slug($title)->lower();
        
        // Ajouter un timestamp pour éviter les doublons
        $slug = $baseSlug . '-' . time();
        
        $entity->setSlug($slug);
    }

    /**
     * Calcule la durée d'un fichier média
     */
    private function calculateMediaDuration(Podcast $podcast, string $fileName): void
    {
        $filePath = $this->fileUploader->getTargetDirectory() . '/podcasts/' . $fileName;
        
        if (!file_exists($filePath)) {
            return;
        }

        try {
            // Utiliser getID3 si disponible
            if (class_exists('\getID3')) {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($filePath);
                
                if (isset($fileInfo['playtime_seconds'])) {
                    $duration = $this->formatDuration($fileInfo['playtime_seconds']);
                    if (method_exists($podcast, 'setCalculatedDuration')) {
                        $podcast->setCalculatedDuration($duration);
                    }
                }
            }
            // Fallback avec ffprobe si disponible
            elseif (function_exists('shell_exec')) {
                $command = "ffprobe -v quiet -show_entries format=duration -of csv=\"p=0\" \"$filePath\" 2>/dev/null";
                $output = shell_exec($command);
                
                if ($output && is_numeric(trim($output))) {
                    $duration = $this->formatDuration((float) trim($output));
                    if (method_exists($podcast, 'setCalculatedDuration')) {
                        $podcast->setCalculatedDuration($duration);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('Erreur calcul durée média: ' . $e->getMessage());
        }
    }

    /**
     * Formate une durée en secondes vers un format lisible
     */
    private function formatDuration(float $seconds): string
    {
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

    /**
     * Vérifie si un fichier est une image
     */
    private function isImageFile(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * Stocke les métadonnées d'un fichier
     */
    private function setFileMetadata($entity, UploadedFile $file): void
    {
        if (method_exists($entity, 'setFileSize')) {
            $entity->setFileSize($file->getSize());
        }
        
        if (method_exists($entity, 'setFileMimeType')) {
            $entity->setFileMimeType($file->getMimeType());
        }
        
        if (method_exists($entity, 'setOriginalFileName')) {
            $entity->setOriginalFileName($file->getClientOriginalName());
        }
    }
}