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

    public function options(array $segmentosUrl): void
    {
        $this->cors();
        http_response_code(204);
    }

    public function get(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    public function post(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    public function put(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    public function delete(array $segmentosUrl): void
    {
        $this->handle($segmentosUrl);
    }

    private function handle(array $segmentosUrl): void
    {
        $this->cors();

        $recurso = '';
        if (isset($segmentosUrl[1]) && $segmentosUrl[1] !== '') {
            $recurso = strtolower(trim((string)$segmentosUrl[1]));
        }
        if ($recurso === '') {
            $recurso = strtolower(trim((string)($_GET['resource'] ?? '')));
        }

        $mapaRecursos = [
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

        if ($recurso === '' || !isset($mapaRecursos[$recurso])) {
            $this->json([
                'sucesso' => false,
                'mensagem' => 'Recurso inválido. Use /api/posts|categorias|usuarios|comentarios|perfil_autor ou ?resource=...',
            ], 404);
            return;
        }

        $config = $mapaRecursos[$recurso];

        try {
            $conexao = (new Database())->conectar();

            $nomeClasse = (string)$config['class'];
            $modelo = new $nomeClasse($conexao);

            $metodo = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

            $id = null;
            if (isset($segmentosUrl[2]) && $segmentosUrl[2] !== '' && ctype_digit((string)$segmentosUrl[2])) {
                $id = (int)$segmentosUrl[2];
            }
            if ($id === null && isset($_GET['id'])) {
                $id = (int)$_GET['id'];
            }

            if ($metodo === 'GET') {
                $resultado = $modelo->get($id);

                if ($id !== null) {
                    if ($resultado === null) {
                        $this->json(['erro' => $config['notFound']], 200);
                        return;
                    }
                    $this->json($resultado, 200);
                    return;
                }

                $this->json($resultado, 200);
                return;
            }

            if ($metodo === 'POST') {
                $sucesso = (bool)$modelo->post($_POST);
                $this->json([
                    'sucesso' => $sucesso,
                    'mensagem' => $sucesso ? $config['messages']['post_ok'] : $config['messages']['post_fail'],
                ], 200);
                return;
            }

            if ($metodo === 'PUT') {
                if ($id === null) {
                    $this->json(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
                    return;
                }

                $corpoBruto = (string)file_get_contents('php://input');
                $dadosPut = [];

                $tipoConteudo = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
                if ($corpoBruto !== '' && str_contains($tipoConteudo, 'application/json')) {
                    $decodificado = json_decode($corpoBruto, true);
                    if (is_array($decodificado)) {
                        $dadosPut = $decodificado;
                    }
                }

                if ($dadosPut === []) {
                    parse_str($corpoBruto, $dadosPut);
                }

                $sucesso = (bool)$modelo->put($id, (array)$dadosPut);

                $this->json([
                    'sucesso' => $sucesso,
                    'mensagem' => $sucesso ? $config['messages']['put_ok'] : $config['messages']['put_fail'],
                ], 200);
                return;
            }

            if ($metodo === 'DELETE') {
                if ($id === null) {
                    $this->json(['sucesso' => false, 'mensagem' => 'Parâmetro id é obrigatório.'], 400);
                    return;
                }

                $sucesso = (bool)$modelo->delete($id);

                $this->json([
                    'sucesso' => $sucesso,
                    'mensagem' => $sucesso ? $config['messages']['delete_ok'] : $config['messages']['delete_fail'],
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
