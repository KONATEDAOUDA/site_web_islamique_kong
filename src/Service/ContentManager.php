<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContentManager
{
    private FileUploader $fileUploader;
    private ImageProcessor $imageProcessor;

    public function __construct(FileUploader $fileUploader, ImageProcessor $imageProcessor)
    {
        $this->fileUploader = $fileUploader;
        $this->imageProcessor = $imageProcessor;
    }

    public function uploadFile(UploadedFile $file, string $directory = ''): string
    {
        return $this->fileUploader->upload($file, $directory);
    }

    public function uploadImage(UploadedFile $file, string $directory = '', string $thumbnailSize = 'medium'): string
    {
        $filename = $this->imageProcessor->processImage($file, $directory);
        
        // Créer des thumbnails si nécessaire
        $imagePath = $this->imageProcessor->getUploadDirectory() . '/' . $directory . '/' . $filename;
        $this->imageProcessor->createThumbnails($imagePath, $thumbnailSize);
        
        return $filename;
    }

    public function handleContentUpload(UploadedFile $file, string $type = 'general'): array
    {
        $directory = match($type) {
            'article' => 'articles',
            'podcast' => 'podcasts',
            'archive' => 'archives',
            'enseignement' => 'enseignements',
            'user' => 'users',
            default => 'general'
        };

        $isImage = in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        
        if ($isImage) {
            $filename = $this->uploadImage($file, $directory);
        } else {
            $filename = $this->uploadFile($file, $directory);
        }

        return [
            'filename' => $filename,
            'type' => $isImage ? 'image' : 'file',
            'directory' => $directory,
            'path' => '/assets/uploads/' . $directory . '/' . $filename
        ];
    }

    public function getFileUploader(): FileUploader
    {
        return $this->fileUploader;
    }

    public function getImageProcessor(): ImageProcessor
    {
        return $this->imageProcessor;
    }
}