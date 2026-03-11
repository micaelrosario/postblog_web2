<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

trait Seguranca
{
    protected function requireAccess(): void
    {
        if (!defined('ACCESS')) {
            http_response_code(403);
            exit('Acesso negado');
        }
    }
}
