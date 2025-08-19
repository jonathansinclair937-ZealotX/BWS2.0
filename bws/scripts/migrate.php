<?php
require_once __DIR__ . '/../packages/shared/db.php';
try {
    $sql = file_get_contents($file);
    $pdo->exec($sql);
    echo "Applied: $base\n";
} catch (PDOException $e) {
    $msg  = $e->getMessage();
    $code = $e->errorInfo[1] ?? null; // MySQL error code

    // Allow idempotent cases and continue
    $ignorable = [1060, /* duplicate column */
                  1061, /* duplicate key name */
                  1062, /* duplicate entry on unique */
                  1091  /* can't drop; check exists */];

    if (in_array($code, $ignorable, true)) {
        echo "Skipped (already applied): $base — $msg\n";
    } else {
        throw $e; // real failure -> stop
    }
}
