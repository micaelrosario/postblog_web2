<?php

declare(strict_types=1);

require_once __DIR__ . '/../database.php';

function fail(string $message, int $code = 1): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

try {
    $db = (new Database())->conectar();

    $tablesStmt = $db->query('SHOW TABLES');
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $schema = [
        'database' => $db->query('SELECT DATABASE()')->fetchColumn(),
        'tables' => [],
    ];

    foreach ($tables as $table) {
        if (!is_string($table) || $table === '') {
            continue;
        }

        $columnsStmt = $db->query('SHOW FULL COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

        $indexesStmt = $db->query('SHOW INDEX FROM `' . str_replace('`', '``', $table) . '`');
        $indexes = $indexesStmt->fetchAll(PDO::FETCH_ASSOC);

        $schema['tables'][$table] = [
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }

    echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    fail('Erro ao inspecionar o banco: ' . $e->getMessage());
}
