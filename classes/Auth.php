<?php

final class Auth
{
    private function conectar(): PDO
    {
        return (new Database())->conectar();
    }

    private function rotaAtual(array $segmentosUrl): string
    {
        return strtolower((string)($segmentosUrl[0] ?? 'login'));
    }

    public function get($segmentosUrl)
    {
        $rota = $this->rotaAtual((array)$segmentosUrl);

        if ($rota === 'logout') {
            $this->logout();
            return;
        }

        if (!empty($_SESSION['usuario_id'])) {
            Http::redirect(Http::baseUrl('/posts'), 303);
            return;
        }

        $titulo = $rota === 'cadastro' ? 'Cadastro' : 'Login';

        Layout::topo($titulo, false);

        $mensagem = (string)($_GET['msg'] ?? '');
        if ($mensagem !== '') {
            $okUrl = (string)($_GET['ok'] ?? '0');
            $tipo = $okUrl === '1' ? 'success' : 'danger';
            echo '<div class="alert alert-' . Http::e($tipo) . '">' . Http::e($mensagem) . '</div>';
        }

        require_once __DIR__ . '/../views/auth.php';

        if ($rota === 'cadastro') {
            AuthView::renderCadastro();
        } else {
            AuthView::renderLogin();
        }

        Layout::rodape();
    }

    public function post($segmentosUrl)
    {
        $rota = $this->rotaAtual((array)$segmentosUrl);

        if ($rota === 'login') {
            $this->handleLogin();
            return;
        }

        if ($rota === 'cadastro') {
            $this->handleCadastro();
            return;
        }

        if ($rota === 'logout') {
            $this->logout();
            return;
        }

        http_response_code(405);
        echo 'Método HTTP não suportado.';
    }

    private function handleLogin(): void
    {
        $username = trim((string)($_POST['username'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');

        if ($username === '' || $senha === '') {
            Http::redirect(
                Http::baseUrl('/login') . '?ok=0&msg=' . rawurlencode('Username e senha são obrigatórios.'),
                303
            );
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloUsuario = new Usuario($conexao);

            $usuario = $modeloUsuario->buscarPorUsername($username);

            if (!$usuario || !password_verify($senha, (string)($usuario['senha'] ?? ''))) {
                Http::redirect(
                    Http::baseUrl('/login') . '?ok=0&msg=' . rawurlencode('Credenciais inválidas.'),
                    303
                );
                return;
            }

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)($usuario['id'] ?? 0);
            $_SESSION['username'] = (string)($usuario['username'] ?? '');
            $_SESSION['login'] = (string)($usuario['username'] ?? '');
            // Mantém "senha" na sessão conforme solicitado (armazenando o HASH do banco, não a senha em texto).
            $_SESSION['senha'] = (string)($usuario['senha'] ?? '');

            Http::redirect(Http::baseUrl('/posts'), 303);
        } catch (PDOException $e) {
            $mensagem = (string)$e->getCode() === '2002'
                ? 'Falha ao conectar no banco de dados. Verifique se o MySQL está rodando e a configuração em config/database.php.'
                : 'Erro ao efetuar login.';
            Http::redirect(
                Http::baseUrl('/login') . '?ok=0&msg=' . rawurlencode($mensagem),
                303
            );
        } catch (Exception $e) {
            Http::redirect(
                Http::baseUrl('/login') . '?ok=0&msg=' . rawurlencode('Erro ao efetuar login.'),
                303
            );
        }
    }

    private function handleCadastro(): void
    {
        $username = trim((string)($_POST['username'] ?? ''));
        $senha = (string)($_POST['senha'] ?? '');
        $firstName = trim((string)($_POST['first_name'] ?? ''));
        $lastName = trim((string)($_POST['last_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($username === '' || $senha === '') {
            Http::redirect(
                Http::baseUrl('/cadastro') . '?ok=0&msg=' . rawurlencode('Username e senha são obrigatórios.'),
                303
            );
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloUsuario = new Usuario($conexao);

            $dados = [
                'username' => $username,
                'senha' => $senha,
                'first_name' => $firstName !== '' ? $firstName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'email' => $email !== '' ? $email : null,
            ];

            $sucesso = (bool)$modeloUsuario->post($dados);
            if (!$sucesso) {
                Http::redirect(
                    Http::baseUrl('/cadastro') . '?ok=0&msg=' . rawurlencode('Não foi possível criar o usuário.'),
                    303
                );
                return;
            }

            // Autentica automaticamente após o cadastro.
            $usuario = $modeloUsuario->buscarPorUsername($username);
            if (!$usuario) {
                Http::redirect(
                    Http::baseUrl('/login') . '?ok=1&msg=' . rawurlencode('Usuário criado. Faça login.'),
                    303
                );
                return;
            }

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)($usuario['id'] ?? 0);
            $_SESSION['username'] = (string)($usuario['username'] ?? '');
            $_SESSION['login'] = (string)($usuario['username'] ?? '');
            // Mantém "senha" na sessão conforme solicitado (armazenando o HASH do banco, não a senha em texto).
            $_SESSION['senha'] = (string)($usuario['senha'] ?? '');

            Http::redirect(
                Http::baseUrl('/posts') . '?ok=1&msg=' . rawurlencode('Conta criada e login efetuado.'),
                303
            );
        } catch (PDOException $e) {
            $codigo = (string)$e->getCode();
            if ($codigo === '23000') {
                $mensagem = 'Username já existe.';
            } elseif ($codigo === '2002') {
                $mensagem = 'Falha ao conectar no banco de dados. Verifique se o MySQL está rodando e a configuração em config/database.php.';
            } else {
                $mensagem = 'Não foi possível criar o usuário.';
            }

            Http::redirect(
                Http::baseUrl('/cadastro') . '?ok=0&msg=' . rawurlencode($mensagem),
                303
            );
        } catch (Exception $e) {
            Http::redirect(
                Http::baseUrl('/cadastro') . '?ok=0&msg=' . rawurlencode('Erro ao criar usuário.'),
                303
            );
        }
    }

    private function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        Http::redirect(
            Http::baseUrl('/login') . '?ok=1&msg=' . rawurlencode('Logout efetuado.'),
            303
        );
    }
}
