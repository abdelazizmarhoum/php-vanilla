<?php
declare(strict_types=1);

use App\Core\Database;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/bootstrap.php';

$pdo = Database::pdo();
$pdo->exec(
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS _migrations (
        id BIGSERIAL PRIMARY KEY,
        filename TEXT UNIQUE NOT NULL,
        applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
    )
    SQL
);

$migrationFiles = glob(__DIR__ . '/migrations/*.sql') ?: [];
usort(
    $migrationFiles,
    static fn (string $left, string $right): int => strcmp(basename($left), basename($right)),
);

$appliedStatement = $pdo->query('SELECT filename FROM _migrations');
$appliedFiles = [];
foreach ($appliedStatement->fetchAll() as $row) {
    $appliedFiles[(string) $row['filename']] = true;
}

if (in_array('--status', $argv ?? [], true)) {
    foreach ($migrationFiles as $migrationFile) {
        $filename = basename($migrationFile);
        $status = isset($appliedFiles[$filename]) ? 'applied' : 'pending';
        echo $filename . ' [' . $status . ']' . PHP_EOL;
    }

    exit(0);
}

$appliedCount = 0;
foreach ($migrationFiles as $migrationFile) {
    $filename = basename($migrationFile);
    if (isset($appliedFiles[$filename])) {
        continue;
    }

    echo 'Applying ' . $filename . '...' . PHP_EOL;
    $sql = file_get_contents($migrationFile);
    if ($sql === false) {
        throw new \RuntimeException('Unable to read migration ' . $filename);
    }

    try {
        $pdo->beginTransaction();
        $pdo->exec($sql);
        $statement = $pdo->prepare(
            'INSERT INTO _migrations (filename) VALUES (:filename)',
        );
        $statement->execute(['filename' => $filename]);
        $pdo->commit();
        $appliedCount++;
    } catch (\PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        fwrite(
            STDERR,
            'Migration ' . $filename . ' failed: ' . $exception->getMessage() . PHP_EOL,
        );
        exit(1);
    }
}

if ($appliedCount === 0) {
    echo 'Nothing to migrate.' . PHP_EOL;
    exit(0);
}

echo 'Applied ' . $appliedCount . ' migration(s)' . PHP_EOL;
