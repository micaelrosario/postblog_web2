<?php

function headerHtml($title) {
    ?>
    <!doctype html>
    <html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <title><?php echo $title; ?> - Meu Blog em PHP</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-dark bg-dark mb-4">
            <div class="container">
                <a class="navbar-brand" href="/posts">Meu Blog</a>
                <div class="navbar-nav flex-row gap-3">
                    <a class="nav-link" href="/posts/novo">Novo post</a>
                    <a class="nav-link" href="/categorias">Categorias</a>
                </div>
            </div>
        </nav>
        <main class="container">
    <?php
}

function footerHtml() {
    ?>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
?>
