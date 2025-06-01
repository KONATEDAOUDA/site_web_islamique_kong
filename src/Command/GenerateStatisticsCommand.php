<?php

namespace App\Command;

use App\Service\AdminStatisticsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-statistics',
    description: 'Generate statistics for the application',
)]
class GenerateStatisticsCommand extends Command
{
    private AdminStatisticsService $statisticsService;

    public function __construct(AdminStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (table, json, csv)', 'table')
            ->addOption('export', 'e', InputOption::VALUE_OPTIONAL, 'Export to file', null)
            ->addOption('period', 'p', InputOption::VALUE_OPTIONAL, 'Period (day, week, month, year)', 'month')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        $exportFile = $input->getOption('export');
        $period = $input->getOption('period');

        $io->title('Generating Application Statistics');

        try {
            // Générer les statistiques
            $stats = $this->generateStats($period);
            
            // Afficher selon le format demandé
            switch ($format) {
                case 'json':
                    $this->outputJson($stats, $io, $exportFile);
                    break;
                case 'csv':
                    $this->outputCsv($stats, $io, $exportFile);
                    break;
                default:
                    $this->outputTable($stats, $io, $exportFile);
                    break;
            }

            $io->success('Statistics generated successfully!');
            
            if ($exportFile) {
                $io->note(sprintf('Statistics exported to: %s', $exportFile));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error generating statistics: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    private function generateStats(string $period): array
    {
        $now = new \DateTime();
        $startDate = match($period) {
            'day' => $now->modify('-1 day'),
            'week' => $now->modify('-1 week'),
            'year' => $now->modify('-1 year'),
            default => $now->modify('-1 month')
        };

        return [
            'total_articles' => $this->statisticsService->getTotalArticles(),
            'total_podcasts' => $this->statisticsService->getTotalPodcasts(),
            'total_archives' => $this->statisticsService->getTotalArchives(),
            'total_enseignements' => $this->statisticsService->getTotalEnseignements(),
            'total_users' => $this->statisticsService->getTotalUsers(),
            'recent_articles' => $this->statisticsService->getRecentArticles($startDate),
            'recent_podcasts' => $this->statisticsService->getRecentPodcasts($startDate),
            'period' => $period,
            'generated_at' => $now->format('Y-m-d H:i:s')
        ];
    }

    private function outputTable(array $stats, SymfonyStyle $io, ?string $exportFile): void
    {
        $rows = [
            ['Total Articles', $stats['total_articles']],
            ['Total Podcasts', $stats['total_podcasts']],
            ['Total Archives', $stats['total_archives']],
            ['Total Enseignements', $stats['total_enseignements']],
            ['Total Users', $stats['total_users']],
            ['Recent Articles (' . $stats['period'] . ')', count($stats['recent_articles'])],
            ['Recent Podcasts (' . $stats['period'] . ')', count($stats['recent_podcasts'])],
        ];

        $io->table(['Metric', 'Value'], $rows);

        if ($exportFile) {
            $content = "Statistics Report - " . $stats['generated_at'] . "\n";
            $content .= str_repeat('=', 50) . "\n\n";
            foreach ($rows as $row) {
                $content .= sprintf("%-30s: %s\n", $row[0], $row[1]);
            }
            file_put_contents($exportFile, $content);
        }
    }

    private function outputJson(array $stats, SymfonyStyle $io, ?string $exportFile): void
    {
        $json = json_encode($stats, JSON_PRETTY_PRINT);
        $io->writeln($json);

        if ($exportFile) {
            file_put_contents($exportFile, $json);
        }
    }

    private function outputCsv(array $stats, SymfonyStyle $io, ?string $exportFile): void
    {
        $csv = "Metric,Value\n";
        $csv .= "Total Articles,{$stats['total_articles']}\n";
        $csv .= "Total Podcasts,{$stats['total_podcasts']}\n";
        $csv .= "Total Archives,{$stats['total_archives']}\n";
        $csv .= "Total Enseignements,{$stats['total_enseignements']}\n";
        $csv .= "Total Users,{$stats['total_users']}\n";
        $csv .= "Recent Articles ({$stats['period']})," . count($stats['recent_articles']) . "\n";
        $csv .= "Recent Podcasts ({$stats['period']})," . count($stats['recent_podcasts']) . "\n";

        $io->writeln($csv);

        if ($exportFile) {
            file_put_contents($exportFile, $csv);
        }
    }
}
