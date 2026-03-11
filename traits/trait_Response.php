<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

trait Response
{
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
