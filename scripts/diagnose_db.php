<?php

require __DIR__ . '/../config/database.php';

try {
    $pdo = (new Database())->conectar();

    echo "OK: conectado\n";

    $info = $pdo->query('SELECT DATABASE() AS db, VERSION() AS ver')->fetch(PDO::FETCH_ASSOC);
    echo 'DB/Version: ' . json_encode($info, JSON_UNESCAPED_UNICODE) . "\n";

    $tabela = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetch(PDO::FETCH_ASSOC);
    echo 'Tabela usuarios: ' . ($tabela ? 'SIM' : 'NÃO') . "\n";

    if ($tabela) {
        $colunas = $pdo->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_ASSOC);
        $nomes = array_map(static fn(array $c): string => (string)($c['Field'] ?? ''), $colunas);
        $nomes = array_values(array_filter($nomes, static fn(string $v): bool => $v !== ''));

        echo 'Colunas: ' . implode(', ', $nomes) . "\n";
    }

    exit(0);
} catch (Throwable $e) {
    echo 'ERRO: ' . get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo 'CODE=' . $e->getCode() . "\n";

    if ($e instanceof PDOException && isset($e->errorInfo)) {
        echo 'errorInfo=' . json_encode($e->errorInfo, JSON_UNESCAPED_UNICODE) . "\n";
    }

    exit(1);
}
