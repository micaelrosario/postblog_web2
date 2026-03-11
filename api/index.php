<?php

declare(strict_types=1);

// Compatibilidade: mantém /api/index.php funcionando, mas delega tudo ao front controller.
if (!defined('ACCESS')) {
    define('ACCESS', true);
}

require __DIR__ . '/../index.php';
