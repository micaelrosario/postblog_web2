<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

trait Response
{
    protected function json(mixed $dados, int $codigoStatus = 200): void
    {
        http_response_code($codigoStatus);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
}
