<?php

final class AuthView
{
    public static function renderLogin(): void
    {
        ?>
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h4 mb-3">Login</h1>

                        <form method="post" action="<?php echo Http::e(Http::baseUrl('/login')); ?>">
                            <?php echo Http::csrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input class="form-control" id="username" name="username" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="senha">Senha</label>
                                <input class="form-control" id="senha" name="senha" type="password" required>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit">Entrar</button>
                                <a class="btn btn-outline-secondary" href="<?php echo Http::e(Http::baseUrl('/cadastro')); ?>">Criar conta</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function renderCadastro(): void
    {
        ?>
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-7 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h4 mb-3">Cadastro</h1>

                        <form method="post" action="<?php echo Http::e(Http::baseUrl('/cadastro')); ?>">
                            <?php echo Http::csrfField(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input class="form-control" id="username" name="username" required autofocus>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="first_name">Nome</label>
                                    <input class="form-control" id="first_name" name="first_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="last_name">Sobrenome</label>
                                    <input class="form-control" id="last_name" name="last_name">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" name="email" type="email">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="senha">Senha</label>
                                <input class="form-control" id="senha" name="senha" type="password" minlength="6" required>
                                <div class="form-text">Mínimo 6 caracteres.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit">Criar conta</button>
                                <a class="btn btn-outline-secondary" href="<?php echo Http::e(Http::baseUrl('/login')); ?>">Já tenho conta</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
