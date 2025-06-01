<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private string $uploadDirectory;
    private SluggerInterface $slugger;

    public function __construct(string $targetDirectory, SluggerInterface $slugger)
    {
        $this->uploadDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, string $directory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $targetDirectory = $this->uploadDirectory;
        if ($directory) {
            $targetDirectory .= '/' . $directory;
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }
        }

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur upload: ' . $e->getMessage());
        }

        return $fileName;
    }

    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }
}