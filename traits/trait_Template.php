<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

trait Template
{
    public function topo(string $titulo): void
    {
        topo($titulo);
    }

    public function rodape(): void
    {
        rodape();
    }
}
