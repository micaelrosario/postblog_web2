<?php

declare(strict_types=1);

defined('ACCESS') or die('Acesso negado');

class Api
{
    use Response;

    private function cors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    public function options(array $dados): void
    {
        $this->cors();
        http_response_code(204);
    }

    public function get(array $dados): void
    {
        $this->handle($dados);
    }

    public function post(array $dados): void
    {
        $this->handle($dados);
    }

    public function put(array $dados): void
    {
        $this->handle($dados);
    }

    public function delete(array $dados): void
    {
        $this->handle($dados);
    }

    private function handle(array $dados): void
    {
        $this->cors();

        $resource = '';
        if (isset($dados[1]) && $dados[1] !== '') {
            $resource = strtolower(trim((string)$dados[1]));
        }
        if ($resource === '') {
            $resource = strtolower(trim((string)($_GET['resource'] ?? '')));
        }

        $resourceMap = [
            'posts' => [
                'class' => 'Post',
                'notFound' => 'Post não encontrado',
                'messages' => [
                    'post_ok' => 'Post criado com sucesso',
                    'post_fail' => 'Erro ao criar post',
                    'put_ok' => 'Post atualizado com sucesso',
                    'put_fail' => 'Erro ao atualizar post',
                    'delete_ok' => 'Post removido com sucesso',
                    'delete_fail' => 'Erro ao remover post',
                ],
            ],
            'categorias' => [
                'class' => 'Categoria',
                'notFound' => 'Categoria não encontrada',
                'messages' => [
                    'post_ok' => 'Categoria criada com sucesso',
                    'post_fail' => 'Erro ao criar categoria',
                    'put_ok' => 'Categoria atualizada com sucesso',
                    'put_fail' => 'Erro ao atualizar categoria',
                    'delete_ok' => 'Categoria removida com sucesso',
                    'delete_fail' => 'Erro ao remover categoria',
                ],
            ],
            'usuarios' => [
                'class' => 'Usuario',
                'notFound' => 'Usuário não encontrado',
                'messages' => [
                    'post_ok' => 'Usuário criado com sucesso',
                    'post_fail' => 'Erro ao criar usuário',
                    'put_ok' => 'Usuário atualizado com sucesso',
                    'put_fail' => 'Erro ao atualizar usuário',
                    'delete_ok' => 'Usuário removido com sucesso',
                    'delete_fail' => 'Erro ao remover usuário',
                ],
            ],
            'comentarios' => [
                'class' => 'Comentario',
                'notFound' => 'Comentário não encontrado',
                'messages' => [
                    'post_ok' => 'Comentário criado com sucesso',
                    'post_fail' => 'Erro ao criar comentário',
                    'put_ok' => 'Comentário atualizado com sucesso',
                    'put_fail' => 'Erro ao atualizar comentário',
                    'delete_ok' => 'Comentário removido com sucesso',
                    'delete_fail' => 'Erro ao remover comentário',
                ],
            ],
            'perfil_autor' => [
                'class' => 'PerfilAutor',
                'notFound' => 'Perfil não encontrado',
                'messages' => [
                    'post_ok' => 'Perfil criado com sucesso',
                    'post_fail' => 'Erro ao criar perfil',
                    'put_ok' => 'Perfil atualizado com sucesso',
                    'put_fail' => 'Erro ao atualizar perfil',
                    'delete_ok' => 'Perfil removido com sucesso',
                    'delete_fail' => 'Erro ao remover perfil',
                ],
            ],
        ];

        if ($resource === '' || !isset($resourceMap[$resource])) {
            $this->json([
                'sucesso' => false,
                'mensagem' => 'Recurso inválido. Use /api/posts|categorias|usuarios|comentarios|perfil_autor ou ?resource=...',
            ], 404);
            return;
        }

        $cfg = $resourceMap[$resource];

        try {
            $con = (new Database())->conectar();

            $className = (string)$cfg['class'];
            $model = new $className($con);

            $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

            $id = null;
            if (isset($dados[2]) && $dados[2] !== '' && ctype_digit((string)$dados[2])) {
                $id = (int)$dados[2];
            }
            if ($id === null && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
            }

            if ($method === 'GET') {
                $result = $model->get($id);

                if ($id !== null) {
                    if ($result === null) {
                        $this->json(['erro' => $cfg['notFound']], 200);
                        return;
                    }
                    $this->json($result, 200);
                    return;
                }

                $this->json($result, 200);
                return;
            }

            if ($method === 'POST') {
                $ok = (bool)$model->post($_POST);
                $this->json([
                    'sucesso' => $ok,
                    'mensagem' => $ok ? $cfg['messages']['post_ok'] : $cfg['messages']['post_fail'],
                ], 200);
                return;
            }

            if ($method === 'PUT') {
                if ($id === null) {
                    $this->json(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
                    return;
                }

                $raw = (string)file_get_contents('php://input');
                $put = [];

                $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
                if ($raw !== '' && str_contains($contentType, 'application/json')) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $put = $decoded;
                    }
                }

                if ($put === []) {
                    parse_str($raw, $put);
                }

                $ok = (bool)$model->put($id, (array)$put);

                $this->json([
                    'sucesso' => $ok,
                    'mensagem' => $ok ? $cfg['messages']['put_ok'] : $cfg['messages']['put_fail'],
                ], 200);
                return;
            }

            if ($method === 'DELETE') {
                if ($id === null) {
                    $this->json(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
                    return;
                }

                $ok = (bool)$model->delete($id);

                $this->json([
                    'sucesso' => $ok,
                    'mensagem' => $ok ? $cfg['messages']['delete_ok'] : $cfg['messages']['delete_fail'],
                ], 200);
                return;
            }

            $this->json(['sucesso' => false, 'mensagem' => 'Método HTTP não suportado.'], 405);
        } catch (Throwable $e) {
            $this->json([
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
