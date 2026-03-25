<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;

#[Signature('backup:database {--path= : Output path for the ZIP file}')]
#[Description('Create a native mysqldump backup and wrap it in a ZIP file')]
class BackupDatabaseCommand extends Command
{
    public function handle(): int
    {
        $connection = Config::get('database.default');
        $config = Config::get("database.connections.{$connection}");

        if (! in_array(($config['driver'] ?? null), ['mysql', 'mariadb'], true)) {
            $this->error('Backup command only supports MySQL/MariaDB connections.');

            return self::FAILURE;
        }

        $outputDir = storage_path('app/backups');
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $timestamp = now()->format('Ymd_His');
        $sqlPath = $outputDir . DIRECTORY_SEPARATOR . ($timestamp . '.sql');
        $zipPath = $this->option('path') ?: ($outputDir . DIRECTORY_SEPARATOR . ($timestamp . '.zip'));
        $dumpBinary = $this->resolveDumpBinary();

        if ($dumpBinary === null) {
            $this->error('Neither mariadbdump nor mysqldump was found in PATH.');

            return self::FAILURE;
        }

        $command = [
            $dumpBinary,
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . ($config['username'] ?? ''),
            '--password=' . ($config['password'] ?? ''),
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--routines',
            '--triggers',
            '--hex-blob',
            '--skip-ssl',
            $config['database'] ?? '',
        ];

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error(trim($process->getErrorOutput()) ?: 'mysqldump failed.');

            return self::FAILURE;
        }

        file_put_contents($sqlPath, $process->getOutput());

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->error('Unable to create ZIP file.');

            @unlink($sqlPath);

            return self::FAILURE;
        }

        $zip->addFile($sqlPath, basename($sqlPath));
        $zip->close();

        @unlink($sqlPath);

        $this->info($zipPath);

        return self::SUCCESS;
    }

    private function resolveDumpBinary(): ?string
    {
        foreach (['mariadbdump', 'mysqldump'] as $binary) {
            $process = new Process([$binary, '--version']);
            $process->setTimeout(5);
            $process->run();

            if ($process->isSuccessful()) {
                return $binary;
            }
        }

        return null;
    }
}
