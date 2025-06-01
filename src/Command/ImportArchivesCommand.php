<?php

namespace App\Command;

use App\Entity\Archive;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'app:import-archives',
    description: 'Import archives from a directory or file',
)]
class ImportArchivesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private FileUploader $fileUploader;

    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'Source directory or file path')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run without actually importing')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Batch size for processing', 50)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');
        $dryRun = $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');

        if (!file_exists($source)) {
            $io->error(sprintf('Source path "%s" does not exist.', $source));
            return Command::FAILURE;
        }

        $io->title('Import Archives Command');
        
        if ($dryRun) {
            $io->warning('DRY RUN MODE - No data will be actually imported');
        }

        if (is_dir($source)) {
            return $this->importFromDirectory($source, $io, $dryRun, $batchSize);
        } else {
            return $this->importFromFile($source, $io, $dryRun);
        }
    }

    private function importFromDirectory(string $directory, SymfonyStyle $io, bool $dryRun, int $batchSize): int
    {
        $files = glob($directory . '/*');
        $imported = 0;
        $skipped = 0;

        $io->progressStart(count($files));

        foreach ($files as $file) {
            if (is_file($file)) {
                try {
                    if (!$dryRun) {
                        $archive = new Archive();
                        $archive->setTitle(basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)));
                        $archive->setDescription('Imported from ' . $file);
                        $archive->setFilePath(basename($file));
                        $archive->setCreatedAt(new \DateTime());
                        
                        $this->entityManager->persist($archive);
                        
                        if ($imported % $batchSize === 0) {
                            $this->entityManager->flush();
                        }
                    }
                    
                    $imported++;
                    $io->progressAdvance();
                } catch (\Exception $e) {
                    $io->error(sprintf('Error importing file "%s": %s', $file, $e->getMessage()));
                    $skipped++;
                }
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $io->progressFinish();
        
        $io->success(sprintf('Import completed! Imported: %d, Skipped: %d', $imported, $skipped));
        
        return Command::SUCCESS;
    }

    private function importFromFile(string $file, SymfonyStyle $io, bool $dryRun): int
    {
        try {
            if (!$dryRun) {
                $archive = new Archive();
                $archive->setTitle(basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION)));
                $archive->setDescription('Imported from ' . $file);
                $archive->setFilePath(basename($file));
                $archive->setCreatedAt(new \DateTime());
                
                $this->entityManager->persist($archive);
                $this->entityManager->flush();
            }
            
            $io->success(sprintf('File "%s" imported successfully!', $file));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error importing file "%s": %s', $file, $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
