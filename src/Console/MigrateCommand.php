<?php

declare(strict_types=1);

namespace App\Console;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateCommand extends Command
{
    private const MIGRATIONS_TABLE = 'migrations';
    private const MIGRATIONS_DIR   = __DIR__ . '/../../migrations';

    public function __construct(private readonly Connection $db)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('migrate')
            ->setDescription('Run pending database migrations')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show pending migrations without running them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Database Migrations');

        $this->ensureMigrationsTable();

        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            $io->success('Nothing to migrate. Database is up to date.');
            return Command::SUCCESS;
        }

        if ($input->getOption('dry-run')) {
            $io->note('Dry run mode — migrations will NOT be executed.');
            $io->listing(array_column($pending, 'name'));
            return Command::SUCCESS;
        }

        $io->section('Running ' . count($pending) . ' migration(s)...');

        foreach ($pending as $migration) {
            $io->write("  <info>→</info> {$migration['name']} ... ");
            try {
                $this->runMigration($migration['path']);
                $this->markAsApplied($migration['name']);
                $io->writeln('<info>OK</info>');
            } catch (\Throwable $e) {
                $io->writeln('<error>FAILED</error>');
                $io->error($e->getMessage());
                return Command::FAILURE;
            }
        }

        $io->success('All migrations applied successfully.');
        return Command::SUCCESS;
    }

    private function ensureMigrationsTable(): void
    {
        $this->db->executeStatement("
            CREATE TABLE IF NOT EXISTS `" . self::MIGRATIONS_TABLE . "` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration`  VARCHAR(255) NOT NULL UNIQUE,
                `applied_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function getAppliedMigrations(): array
    {
        return $this->db->fetchFirstColumn(
            'SELECT migration FROM ' . self::MIGRATIONS_TABLE . ' ORDER BY migration ASC'
        );
    }

    private function getPendingMigrations(): array
    {
        $migrationsDir = self::MIGRATIONS_DIR;

        if (!is_dir($migrationsDir)) {
            return [];
        }

        $files   = glob($migrationsDir . '/*.sql');
        sort($files);
        $applied = $this->getAppliedMigrations();
        $pending = [];

        foreach ($files as $file) {
            $name = basename($file);
            if (!in_array($name, $applied, true)) {
                $pending[] = ['name' => $name, 'path' => $file];
            }
        }

        return $pending;
    }

    private function runMigration(string $path): void
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new \RuntimeException("Cannot read migration file: $path");
        }

        // Split on semicolons to handle multiple statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn(string $s) => $s !== '' && !str_starts_with($s, '--')
        );

        $this->db->beginTransaction();
        try {
            foreach ($statements as $statement) {
                if (trim($statement) !== '') {
                    $this->db->executeStatement($statement);
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function markAsApplied(string $migration): void
    {
        $this->db->insert(self::MIGRATIONS_TABLE, [
            'migration'  => $migration,
            'applied_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
