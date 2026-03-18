<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function classificarImagem(?string $imagem): string
{
    $imagem = trim((string)$imagem);

    if ($imagem === '') {
        return 'VAZIO';
    }

    if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $imagem) === 1) {
        // Ex.: https:, http:, data:, file:
        return 'URL_ABSOLUTA';
    }

    if (str_starts_with($imagem, '//')) {
        return 'URL_ABSOLUTA';
    }

    if (preg_match('/^[a-zA-Z]:[\\/]/', $imagem) === 1) {
        // Ex.: C:\Users\... ou C:/...
        return 'CAMINHO_WINDOWS';
    }

    if (str_contains($imagem, '\\')) {
        return 'BARRAS_INVERTIDAS';
    }

    if (str_starts_with($imagem, '/')) {
        return 'CAMINHO_ROOT';
    }

    return 'CAMINHO_RELATIVO';
}

try {
    $pdo = (new Database())->conectar();

    echo "OK: conectado" . PHP_EOL;

    $tabelas = $pdo->query("SHOW TABLES LIKE 'post'")->fetchAll();
    $temPost = count($tabelas) > 0;
    echo 'Tabela post: ' . ($temPost ? 'SIM' : 'NÃO') . PHP_EOL;

    if (!$temPost) {
        exit(0);
    }

    $rows = $pdo
        ->query('SELECT id, titulo, imagem, criado_em FROM post ORDER BY criado_em DESC LIMIT 20')
        ->fetchAll(PDO::FETCH_ASSOC);

    echo 'Posts (id | tipo | imagem):' . PHP_EOL;
    if (count($rows) === 0) {
        echo "(sem registros)" . PHP_EOL;
        exit(0);
    }

    foreach ($rows as $r) {
        $id = (int)($r['id'] ?? 0);
        $imagem = (string)($r['imagem'] ?? '');
        $tipo = classificarImagem($imagem);

        // Limita a saída para não poluir o terminal
        $imgCurto = $imagem;
        if (function_exists('mb_strlen') && mb_strlen($imgCurto, 'UTF-8') > 120) {
            $imgCurto = mb_substr($imgCurto, 0, 120, 'UTF-8') . '...';
        } elseif (strlen($imgCurto) > 120) {
            $imgCurto = substr($imgCurto, 0, 120) . '...';
        }

        echo $id . ' | ' . $tipo . ' | ' . $imgCurto . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ERRO: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
