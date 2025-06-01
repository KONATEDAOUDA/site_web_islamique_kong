<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageProcessor
{
    private string $uploadDirectory;
    private array $thumbnailSizes;

    public function __construct(string $uploadDirectory, array $thumbnailSizes)
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->thumbnailSizes = $thumbnailSizes;
    }

    public function processImage(UploadedFile $file, string $subdirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = uniqid() . '.' . $file->guessExtension();
        
        $targetDirectory = $this->uploadDirectory;
        if ($subdirectory) {
            $targetDirectory .= '/' . $subdirectory;
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }
        }
        
        // Déplacer le fichier
        $file->move($targetDirectory, $filename);
        
        return $filename;
    }

    public function createThumbnails(string $imagePath, string $size = 'medium'): string
    {
        if (!isset($this->thumbnailSizes[$size])) {
            throw new \InvalidArgumentException("Taille de thumbnail '$size' non supportée");
        }

        $sizeConfig = $this->thumbnailSizes[$size];
        // Ici vous pouvez ajouter la logique de redimensionnement
        // Pour l'instant, on retourne juste le nom du fichier
        
        return $imagePath;
    }

    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }

    public function getThumbnailSizes(): array
    {
        return $this->thumbnailSizes;
    }
}