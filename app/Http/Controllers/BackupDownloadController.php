<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupDownloadController extends Controller
{
    public function __invoke(): Response
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $database = $connection->getDatabaseName();
        $tableNames = collect([
            (new User())->getTable(),
            (new Domain())->getTable(),
            (new DomainEmail())->getTable(),
        ])->unique()->values();

        $tempDir = storage_path('app/tmp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $timestamp = now()->format('Ymd_His');
        $sqlPath = $tempDir . DIRECTORY_SEPARATOR . $database . '-' . $timestamp . '.sql';
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $database . '-' . $timestamp . '.zip';
        $sqlHandle = fopen($sqlPath, 'wb');

        fwrite($sqlHandle, '-- Backup generated at ' . now()->toDateTimeString() . PHP_EOL);
        fwrite($sqlHandle, 'SET NAMES utf8mb4;' . PHP_EOL);
        fwrite($sqlHandle, 'SET FOREIGN_KEY_CHECKS=0;' . PHP_EOL);
        fwrite($sqlHandle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';" . PHP_EOL);
        fwrite($sqlHandle, 'START TRANSACTION;' . PHP_EOL . PHP_EOL);

        foreach ($tableNames as $tableName) {
            $createTable = (array) $connection->selectOne("SHOW CREATE TABLE `{$tableName}`");
            fwrite($sqlHandle, "DROP TABLE IF EXISTS `{$tableName}`;" . PHP_EOL);
            fwrite($sqlHandle, $createTable['Create Table'] . ';' . PHP_EOL);

            $rows = $connection->table($tableName)->get();

            foreach ($rows as $row) {
                $row = (array) $row;
                $columns = '`' . implode('`, `', array_keys($row)) . '`';
                $values = array_map(function ($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }

                    return $pdo->quote((string) $value);
                }, array_values($row));

                fwrite($sqlHandle, "INSERT INTO `{$tableName}` ({$columns}) VALUES (" . implode(', ', $values) . ');' . PHP_EOL);
            }

            fwrite($sqlHandle, PHP_EOL);
        }

        fwrite($sqlHandle, 'COMMIT;' . PHP_EOL);
        fwrite($sqlHandle, 'SET FOREIGN_KEY_CHECKS=1;' . PHP_EOL);
        fclose($sqlHandle);

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($sqlPath, basename($sqlPath));
        $zip->close();
        @unlink($sqlPath);

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }
}
