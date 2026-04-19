<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use App\Actions\Campaigns\RunQueueWorkStopWhenEmptyAction;
use Illuminate\Database\ConnectionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArtisanController extends Controller
{
    public function clear(): RedirectResponse
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');

        if (! file_exists(base_path('composer.phar'))) {
            return redirect()->route('admin.dashboard')->with('status', 'Cache cleared, pero no se encontro composer.phar.');
        }

        $composerUpdate = Process::path(base_path())
            ->timeout(1800)
            ->run(['php', 'composer.phar', 'update', '--no-interaction']);

        if ($composerUpdate->failed()) {
            return redirect()->route('admin.dashboard')->with('status', 'Cache cleared, pero composer.phar update ha fallado.');
        }

        return redirect()->route('admin.dashboard')->with('status', 'Cache cleared + composer.phar update ejecutado.');
    }

    public function migration_and_seeds(): RedirectResponse
    {
        Artisan::call('migrate --force');
        Artisan::call('db:seed --force');

        return redirect()->route('admin.dashboard')->with('status', 'Database migrated!');
    }

    public function queueWorkStopWhenEmpty(RunQueueWorkStopWhenEmptyAction $runQueueWorkStopWhenEmptyAction): RedirectResponse
    {
        if (! $runQueueWorkStopWhenEmptyAction->execute()) {
            return redirect()->route('admin.dashboard')->with('status', __('admin.queue.status_failed'));
        }

        return redirect()->route('admin.dashboard')->with('status', __('admin.queue.status_finished'));
    }

    public function downloadDatabaseCopy(): BinaryFileResponse|StreamedResponse
    {
        $connectionName = (string) config('database.default');
        $connectionConfig = config("database.connections.{$connectionName}");

        if (! is_array($connectionConfig)) {
            throw new HttpResponseException(response(__('admin.database_copy.errors.connection_not_found'), 500));
        }

        $driver = (string) ($connectionConfig['driver'] ?? '');

        if ($driver === 'sqlite') {
            return $this->downloadSqliteBackup($connectionConfig);
        }

        if ($driver === 'mysql') {
            return $this->downloadMysqlBackup($connectionName, $connectionConfig);
        }

        throw new HttpResponseException(response(__('admin.database_copy.errors.unsupported_driver'), 500));
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function downloadSqliteBackup(array $connectionConfig): BinaryFileResponse
    {
        $databasePath = (string) ($connectionConfig['database'] ?? '');

        if ($databasePath === '' || $databasePath === ':memory:') {
            throw new HttpResponseException(response(__('admin.database_copy.errors.sqlite_in_memory'), 422));
        }

        if (! str_starts_with($databasePath, '/')) {
            $databasePath = database_path($databasePath);
        }

        if (! is_file($databasePath)) {
            throw new HttpResponseException(response(__('admin.database_copy.errors.sqlite_file_missing'), 404));
        }

        return response()->download(
            $databasePath,
            $this->buildBackupFilename('sqlite-backup', 'sqlite'),
            ['Content-Type' => 'application/octet-stream']
        );
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function downloadMysqlBackup(string $connectionName, array $connectionConfig): StreamedResponse
    {
        $databaseName = (string) ($connectionConfig['database'] ?? '');

        if ($databaseName === '') {
            throw new HttpResponseException(response(__('admin.database_copy.errors.database_name_missing'), 500));
        }

        $host = (string) ($connectionConfig['host'] ?? '127.0.0.1');
        $port = (string) ($connectionConfig['port'] ?? '3306');
        $username = (string) ($connectionConfig['username'] ?? '');
        $password = (string) ($connectionConfig['password'] ?? '');
        $dumpExecutable = $this->resolveMysqlDumpExecutable();

        if ($dumpExecutable === null) {
            return $this->streamMysqlBackupWithoutCli($connectionName, $databaseName);
        }

        $process = Process::timeout(120)
            ->env($password !== '' ? ['MYSQL_PWD' => $password] : [])
            ->run([
                $dumpExecutable,
                '--single-transaction',
                '--quick',
                '--skip-comments',
                '--no-tablespaces',
                "--host={$host}",
                "--port={$port}",
                "--user={$username}",
                $databaseName,
            ]);

        if ($process->failed()) {
            throw new HttpResponseException(response(__('admin.database_copy.errors.dump_failed'), 500));
        }

        $sqlDump = $process->output();

        return response()->streamDownload(
            static function () use ($sqlDump): void {
                echo $sqlDump;
            },
            $this->buildBackupFilename($databaseName . '-backup', 'sql'),
            ['Content-Type' => 'application/sql; charset=UTF-8']
        );
    }

    private function resolveMysqlDumpExecutable(): ?string
    {
        $result = Process::timeout(10)
            ->run(['sh', '-lc', 'command -v mysqldump || command -v mariadb-dump']);

        if ($result->failed()) {
            return null;
        }

        $binaryPath = trim($result->output());

        if ($binaryPath === '') {
            return null;
        }

        return $binaryPath;
    }

    private function streamMysqlBackupWithoutCli(string $connectionName, string $databaseName): StreamedResponse
    {
        return response()->streamDownload(
            function () use ($connectionName, $databaseName): void {
                $connection = DB::connection($connectionName);
                $pdo = $connection->getPdo();

                echo sprintf("-- SQL backup generated at %s\n", now()->toDateTimeString());
                echo sprintf("-- Database: %s\n\n", $databaseName);
                echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

                foreach ($this->mysqlTableNames($connection) as $tableName) {
                    $escapedTable = $this->escapeSqlIdentifier($tableName);
                    $createStatement = $this->mysqlCreateTableStatement($connection, $tableName);

                    echo sprintf("DROP TABLE IF EXISTS `%s`;\n", $escapedTable);
                    echo $createStatement . ";\n\n";

                    foreach ($connection->table($tableName)->cursor() as $row) {
                        $rowData = get_object_vars($row);

                        if ($rowData === []) {
                            continue;
                        }

                        $columns = array_map(
                            fn(string $column): string => sprintf('`%s`', $this->escapeSqlIdentifier($column)),
                            array_keys($rowData)
                        );

                        $values = array_map(
                            fn(mixed $value): string => $this->quoteSqlValue($pdo, $value),
                            array_values($rowData)
                        );

                        echo sprintf(
                            "INSERT INTO `%s` (%s) VALUES (%s);\n",
                            $escapedTable,
                            implode(', ', $columns),
                            implode(', ', $values)
                        );
                    }

                    echo "\n";
                }

                echo "SET FOREIGN_KEY_CHECKS=1;\n";
            },
            $this->buildBackupFilename($databaseName . '-backup', 'sql'),
            ['Content-Type' => 'application/sql; charset=UTF-8']
        );
    }

    /**
     * @return array<int, string>
     */
    private function mysqlTableNames(ConnectionInterface $connection): array
    {
        $rows = $connection->select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']);

        return array_values(array_filter(array_map(function (object $row): ?string {
            $values = array_values(get_object_vars($row));

            if ($values === []) {
                return null;
            }

            $tableName = $values[0];

            return is_string($tableName) && $tableName !== '' ? $tableName : null;
        }, $rows)));
    }

    private function mysqlCreateTableStatement(ConnectionInterface $connection, string $tableName): string
    {
        $escapedTable = $this->escapeSqlIdentifier($tableName);
        $createRow = $connection->selectOne(sprintf('SHOW CREATE TABLE `%s`', $escapedTable));

        if (! is_object($createRow)) {
            throw new HttpResponseException(response(__('admin.database_copy.errors.dump_failed'), 500));
        }

        foreach (get_object_vars($createRow) as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            if (str_starts_with((string) $key, 'Create ')) {
                return $value;
            }
        }

        throw new HttpResponseException(response(__('admin.database_copy.errors.dump_failed'), 500));
    }

    private function escapeSqlIdentifier(string $identifier): string
    {
        return str_replace('`', '``', $identifier);
    }

    private function quoteSqlValue(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $quoted = $pdo->quote((string) $value);

        return $quoted === false ? "''" : $quoted;
    }

    private function buildBackupFilename(string $baseName, string $extension): string
    {
        return sprintf('%s-%s.%s', $baseName, now()->format('Ymd-His'), $extension);
    }
}
