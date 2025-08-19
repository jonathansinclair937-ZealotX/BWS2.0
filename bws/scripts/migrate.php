<?php
// bws/scripts/migrate.php
declare(strict_types=1);

/**
 * Minimal, portable SQL migration runner for MySQL/MariaDB.
 * - Discovers ./database/migrations/*.sql (relative to repo root 'bws')
 * - Applies in filename order
 * - Tracks applied migrations in 'migrations' table
 * - Ignores idempotent errors (duplicate column/index, can't drop, etc.)
 */

// ---------- locate paths ----------
$here = __DIR__;                       // .../bws/scripts
$root = dirname($here);                // .../bws
$dir  = $root . '/database/migrations';

if (!is_dir($dir)) {
    fwrite(STDERR, "Migrations directory not found: $dir\n");
    exit(1);
}

// ---------- load env ----------
function env(string $key, ?string $default = null): ?string {
    $v = getenv($key);
    return $v === false ? $default : $v;
}

$dbHost = env('DB_HOST', '127.0.0.1');
$dbPort = (int)env('DB_PORT', '3306');
$dbName = env('DB_NAME');           // REQUIRED
$dbUser = env('DB_USER');           // REQUIRED
$dbPass = env('DB_PASS', '');

// If not present in environment, try reading bws/.env (KEY=VALUE lines)
$dotenv = $root . '/.env';
if ((!$dbName || !$dbUser) && is_file($dotenv)) {
    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if (!getenv($k)) {
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
$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
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
foreach ($stmt as $row) $applied[$row['filename']] = true;

// ---------- collect migration files ----------
$files = glob($dir . '/*.sql');
sort($files, SORT_NATURAL);

if (!$files) {
    echo "No migrations found in $dir\n";
    exit(0);
}

// ---------- run each migration ----------
$IGNORABLE = [1060, /* duplicate column */
              1061, /* duplicate key name */
              1062, /* duplicate entry */
              1091  /* can't drop constraint/column */];

foreach ($files as $file) {
    $base = basename($file);
    if (isset($applied[$base])) {
        echo "Already applied: $base\n";
        continue;
    }

    echo "Applying migration: $base\n";

    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        echo "Skipped empty or unreadable file: $base\n";
        continue;
    }

    try {
        // Some migration files may contain multiple statements.
        // Split on semicolons that terminate statements.
        $statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/m', $sql)));
        if (empty($statements)) $statements = [trim($sql)];

        $pdo->beginTransaction();
        foreach ($statements as $stmtSql) {
            if ($stmtSql === '') continue;
            try {
                $pdo->exec($stmtSql);
            } catch (PDOException $ex) {
                $code = $ex->errorInfo[1] ?? null;
                if (in_array($code, $IGNORABLE, true)) {
                    echo "  Ignored idempotent error ($code): " . $ex->getMessage() . "\n";
                    continue;
                }
                throw $ex;
            }
        }
        $mark = $pdo->prepare("INSERT INTO migrations (filename) VALUES (:f)");
        $mark->execute([':f' => $base]);
        $pdo->commit();

        echo "Applied: $base\n";
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "All migrations complete.\n";
