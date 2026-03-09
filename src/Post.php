<?php
class Post {
    private $conn;
    private $tableName = "post";

    public $id;
    public $titulo;
    public $conteudo;
    public $imagem;
    public $criado_em;
    use CodeHtml;

    public function __construct($db){ # construtor para receber a conexão com o banco de dados
        $this->conn = $db;
    }

    public function get($metodo){
        if ($metodo == "GET") { #método para Listar os posts
            
            $stmt = $this->conn->prepare("SELECT * FROM {$this->tableName} ORDER BY criado_em DESC");
            $stmt->execute();
            $num = $stmt->rowCount();

            if ($num > 0) { # verificar se há posts para exibir
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    echo "<div class='card mb-3'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>{$titulo}</h5>";
                    echo "<p class='card-text'>{$conteudo}</p>";
                    echo "<p class='card-text'><small class='text-muted'>Publicado em: {$criado_em}</small></p>";
                    echo "</div>";
                    echo "</div>";
                }
            } else { # caso não haja posts, exibir mensagem na tela
                echo "<p>Nenhum post foi encontrado.</p>";
            }
            
            $this->rodape();
        }
    }

    public function post($metodo, $data){
        if ($metodo == "POST") { #método para criar um novo post
            $stmt = $this->conn->prepare("INSERT INTO {$this->tableName} (titulo, conteudo, imagem, criado_em) VALUES (:titulo, :conteudo, :imagem, NOW())");
            $stmt->bindParam(':titulo', $data['titulo']);
            $stmt->bindParam(':conteudo', $data['conteudo']);
            $stmt->bindParam(':imagem', $data['imagem']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Post criado com sucesso!"]);
            } else {
                echo json_encode(["message" => "Erro ao criar o post."]);
            }
        }
    }
}
?>