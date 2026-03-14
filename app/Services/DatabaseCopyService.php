<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseCopyService
{
    /**
     * @return array{tables:int, rows:int, per_table:array<string, int>}
     */
    public function copy(string $sourceConnection, string $targetConnection, bool $truncate = true, int $batchSize = 500): array
    {
        if ($batchSize < 1) {
            throw new RuntimeException('Batch size must be at least 1.');
        }

        $source = DB::connection($sourceConnection);
        $target = DB::connection($targetConnection);

        $tables = $this->sourceTables($sourceConnection);

        if ($tables === []) {
            throw new RuntimeException('No source tables were found to copy.');
        }

        $missingTables = array_values(array_filter(
            $tables,
            static fn (string $table): bool => ! Schema::connection($targetConnection)->hasTable($table),
        ));

        if ($missingTables !== []) {
            throw new RuntimeException(
                'Target connection is missing tables: '.implode(', ', $missingTables).'. Run migrations on the target database first.'
            );
        }

        $this->disableForeignKeyChecks($targetConnection);

        try {
            if ($truncate) {
                foreach ($tables as $table) {
                    $target->table($table)->delete();
                }
            }

            $totalRows = 0;
            $perTable = [];

            foreach ($tables as $table) {
                $sourceColumns = Schema::connection($sourceConnection)->getColumnListing($table);
                $targetColumns = Schema::connection($targetConnection)->getColumnListing($table);
                $columns = array_values(array_intersect($sourceColumns, $targetColumns));

                $offset = 0;
                $copied = 0;

                while (true) {
                    $rows = $source->table($table)
                        ->offset($offset)
                        ->limit($batchSize)
                        ->get($columns)
                        ->map(static fn (object $row): array => (array) $row)
                        ->all();

                    if ($rows === []) {
                        break;
                    }

                    $target->table($table)->insert($rows);

                    $count = count($rows);
                    $copied += $count;
                    $totalRows += $count;
                    $offset += $count;
                }

                $perTable[$table] = $copied;
            }
        } finally {
            $this->enableForeignKeyChecks($targetConnection);
        }

        return [
            'tables' => count($tables),
            'rows' => $totalRows,
            'per_table' => $perTable,
        ];
    }

    /**
     * @return list<string>
     */
    public function sourceTables(string $sourceConnection): array
    {
        $driver = config("database.connections.{$sourceConnection}.driver");

        if ($driver !== 'sqlite') {
            throw new RuntimeException(sprintf('Source connection [%s] must use sqlite.', $sourceConnection));
        }

        return array_map(
            static fn (object $row): string => $row->name,
            DB::connection($sourceConnection)->select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            ),
        );
    }

    private function disableForeignKeyChecks(string $connection): void
    {
        match (config("database.connections.{$connection}.driver")) {
            'mysql', 'mariadb' => DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0'),
            'sqlite' => DB::connection($connection)->statement('PRAGMA foreign_keys = OFF'),
            default => null,
        };
    }

    private function enableForeignKeyChecks(string $connection): void
    {
        match (config("database.connections.{$connection}.driver")) {
            'mysql', 'mariadb' => DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1'),
            'sqlite' => DB::connection($connection)->statement('PRAGMA foreign_keys = ON'),
            default => null,
        };
    }
}
