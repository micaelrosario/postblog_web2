<?php

final class Auth
{
    private function conectar(): PDO
    {
        return (new Database())->conectar();
    }

    private function iniciarSessaoUsuario(array $usuario): void
    {
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = (int)($usuario['id'] ?? 0);

        $username = (string)($usuario['username'] ?? '');
        $_SESSION['username'] = $username;
        $_SESSION['login'] = $username;

        // Mantém "senha" na sessão conforme solicitado (armazenando o HASH do banco, não a senha em texto).
        $_SESSION['senha'] = (string)($usuario['senha'] ?? '');
    }

    private function rotaAtual(array $segmentosUrl): string
    {
        return strtolower((string)($segmentosUrl[0] ?? 'login'));
    }

    public function get(array $segmentosUrl): void
    {
        $rota = $this->rotaAtual($segmentosUrl);

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

        require_once __DIR__ . '/../views/auth.php';

        if ($rota === 'cadastro') {
            AuthView::renderCadastro();
        } else {
            AuthView::renderLogin();
        }

        Layout::rodape();
    }

    public function post(array $segmentosUrl): void
    {
        $rota = $this->rotaAtual($segmentosUrl);

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
            Http::setFlash('Username e senha são obrigatórios.', 'danger');
            Http::redirect(Http::baseUrl('/login'), 303);
            return;
        }

        try {
            $conexao = $this->conectar();
            $modeloUsuario = new Usuario($conexao);

            $usuario = $modeloUsuario->buscarPorUsername($username);

            if (!$usuario || !password_verify($senha, (string)($usuario['senha'] ?? ''))) {
                Http::setFlash('Credenciais inválidas.', 'danger');
                Http::redirect(Http::baseUrl('/login'), 303);
                return;
            }

            $this->iniciarSessaoUsuario($usuario);

            Http::redirect(Http::baseUrl('/posts'), 303);
        } catch (PDOException $e) {
            $mensagem = (string)$e->getCode() === '2002'
                ? 'Falha ao conectar no banco de dados. Verifique se o MySQL está rodando e a configuração em config/database.php.'
                : 'Erro ao efetuar login.';
            Http::setFlash($mensagem, 'danger');
            Http::redirect(Http::baseUrl('/login'), 303);
        } catch (Exception $e) {
            Http::setFlash('Erro ao efetuar login.', 'danger');
            Http::redirect(Http::baseUrl('/login'), 303);
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
            Http::setFlash('Username e senha são obrigatórios.', 'danger');
            Http::redirect(Http::baseUrl('/cadastro'), 303);
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
                Http::setFlash('Não foi possível criar o usuário.', 'danger');
                Http::redirect(Http::baseUrl('/cadastro'), 303);
                return;
            }

            // Autentica automaticamente após o cadastro.
            $usuario = $modeloUsuario->buscarPorUsername($username);
            if (!$usuario) {
                Http::setFlash('Usuário criado. Faça login.', 'success');
                Http::redirect(Http::baseUrl('/login'), 303);
                return;
            }

            $this->iniciarSessaoUsuario($usuario);

            Http::setFlash('Conta criada e login efetuado.', 'success');
            Http::redirect(Http::baseUrl('/posts'), 303);
        } catch (PDOException $e) {
            $codigo = (string)$e->getCode();
            if ($codigo === '23000') {
                $mensagem = 'Username já existe.';
            } elseif ($codigo === '2002') {
                $mensagem = 'Falha ao conectar no banco de dados. Verifique se o MySQL está rodando e a configuração em config/database.php.';
            } else {
                $mensagem = 'Não foi possível criar o usuário.';
            }

            Http::setFlash($mensagem, 'danger');
            Http::redirect(Http::baseUrl('/cadastro'), 303);
        } catch (Exception $e) {
            Http::setFlash('Erro ao criar usuário.', 'danger');
            Http::redirect(Http::baseUrl('/cadastro'), 303);
        }
    }

    private function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        Http::setFlash('Logout efetuado.', 'success');
        Http::redirect(Http::baseUrl('/login'), 303);
    }
}
