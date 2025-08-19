<?php
// bws/scripts/migrate.php
declare(strict_types=1);

/**
 * Portable MySQL/MariaDB migration runner.
 * - Discovers ./database/migrations/*.sql (relative to repo folder 'bws')
 * - Applies in natural filename order
 * - Tracks applied migrations in 'migrations' table
 * - Ignores common idempotent errors (duplicate column/index, can't drop)
 * - Works even if the storage engine does not support transactions
 */

// ---------- locate paths ----------
$here = __DIR__;                       // .../bws/scripts
$root = dirname($here);                // .../bws
$migrationsDir  = $root . '/database/migrations';

if (!is_dir($migrationsDir)) {
    fwrite(STDERR, "Migrations directory not found: $migrationsDir\n");
    exit(1);
}

// ---------- env helpers ----------
function envval(string $key, ?string $default = null): ?string {
    $v = getenv($key);
    return ($v === false) ? $default : $v;
}

// ---------- load env from process, then fallback to bws/.env ----------
$dbHost = envval('DB_HOST', '127.0.0.1');
$dbPort = (int) envval('DB_PORT', '3306');
$dbName = envval('DB_NAME') ?: null;
$dbUser = envval('DB_USER') ?: null;
$dbPass = envval('DB_PASS', '');

// If not present in environment, try reading bws/.env (simple KEY=VALUE lines)
$dotenv = $root . '/.env';
if ((!$dbName || !$dbUser) && is_file($dotenv)) {
    $lines = @file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '' || $t[0] === '#') continue;
        if (!str_contains($t, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $t, 2));
        // don't override existing env
        if (getenv($k) === false) {
            putenv("$k=$v");
            if ($k === 'DB_HOST') $dbHost = $v;
            if ($k === 'DB_PORT') $dbPort = (int)$v;
            if ($k === 'DB_NAME') $dbName = $v;
            if ($k === 'DB_USER') $dbUser = $v;
            if ($k === 'DB_PASS') $dbPass = $v;
        }
    }
}

if (!$dbName || !$dbUser) {
    fwrite(STDERR, "Missing DB_NAME/DB_USER in environment or .env\n");
    exit(1);
}

// ---------- connect ----------
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connect failed: " . $e->getMessage() . "\n");
    exit(1);
}

// ---------- migrations table ----------
$pdo->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// fetch already applied
$applied = [];
$stmt = $pdo->query("SELECT filename FROM migrations");
foreach ($stmt as $row) {
    $applied[$row['filename']] = true;
}

// ---------- collect migration files ----------
$files = glob($migrationsDir . '/*.sql');
sort($files, SORT_NATURAL | SORT_FLAG_CASE);

if (!$files) {
    echo "No migrations found in {$migrationsDir}\n";
    exit(0);
}

// MySQL error codes that are safe to ignore for idempotency
$IGNORABLE = [
    1060, // duplicate column name
    1061, // duplicate key name
    1062, // duplicate entry for key
    1091, // can't drop; check that column/key exists
];

// ---------- run each migration ----------
foreach ($files as $path) {
    $base = basename($path);
    if (isset($applied[$base])) {
        echo "Already applied: {$base}\n";
        continue;
    }

    echo "Applying migration: {$base}\n";

    $sql = file_get_contents($path);
    if ($sql === false || trim($sql) === '') {
        echo "Skipped empty or unreadable file: {$base}\n";
        continue;
    }

    // Split SQL by statement terminators (simple heuristic)
    $statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/m', $sql)));
    if (empty($statements)) $statements = [trim($sql)];

    // Try to start a transaction; some storage engines may not support it
    $txStarted = false;
    try {
        $txStarted = $pdo->beginTransaction();
    } catch (Throwable $__) {
        $txStarted = false;
    }

    try {
        foreach ($statements as $stmtSql) {
            if ($stmtSql === '') continue;
            try {
                $pdo->exec($stmtSql);
            } catch (PDOException $ex) {
                $code = $ex->errorInfo[1] ?? null;
                if (in_array($code, $IGNORABLE, true)) {
                    echo "  Ignored idempotent error ({$code}): " . $ex->getMessage() . "\n";
                    continue;
                }
                throw $ex;
            }
        }

        // Mark as applied
        $mark = $pdo->prepare("INSERT INTO migrations (filename) VALUES (:f)");
        $mark->execute([':f' => $base]);

        if ($txStarted && $pdo->inTransaction()) {
            $pdo->commit();
        }

        echo "Applied: {$base}\n";
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            try { $pdo->rollBack(); } catch (Throwable $__) {}
        }
        fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "All migrations complete.\n";
