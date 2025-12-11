# 📰 BlogPost - Sistema de Gerenciamento de Blog

Sistema completo de gerenciamento de blog com API REST implementando todos os métodos HTTP (GET, POST, PUT, DELETE) e interface estilizada com Bootstrap 5.

## 🚀 Características

- ✅ **API REST completa** com suporte a GET, POST, PUT e DELETE
- ✅ **Interface moderna** com Bootstrap 5 e Bootstrap Icons
- ✅ **CRUD completo** para Posts, Categorias, Usuários, Comentários e Perfis de Autor
- ✅ **Headers CORS** configurados
- ✅ **Respostas em JSON** padronizadas
- ✅ **Sistema de notificações** com Toast Bootstrap
- ✅ **Documentação completa** da API

## 📋 Pré-requisitos

- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno
- Banco de dados MySQL

## 🔧 Instalação

1. **Clone o repositório** na pasta `htdocs` do XAMPP:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/micaelrosario/postblog_web2.git
   ```

2. **Importe o banco de dados**:
   - Abra o phpMyAdmin: `http://localhost/phpmyadmin`
   - Crie um novo banco chamado `blogpost`
   - Importe o arquivo `sql/blogpost.sql`

3. **Configure a conexão** (se necessário):
   - Edite `src/database.php`
   - Ajuste as credenciais do banco de dados

4. **Inicie o XAMPP**:
   - Inicie o Apache
   - Inicie o MySQL

## 🎯 Como Usar

### Acesso ao Sistema

1. Abra o navegador e acesse: `http://localhost/postblog_web2/public/`
2. Você verá o painel principal com acesso a todos os módulos

### Páginas Disponíveis

| Página | Descrição | URL |
|--------|-----------|-----|
| **Home** | Painel principal | `index.html` |
| **Posts** | Gerenciar posts do blog | `posts.html` |
| **Categorias** | Gerenciar categorias | `categorias.html` |
| **Usuários** | Gerenciar usuários | `usuarios.html` |
| **Comentários** | Gerenciar comentários | `comentarios.html` |
| **Perfis** | Gerenciar perfis de autor | `perfis.html` |
| **API Docs** | Documentação da API | `api-docs.html` |

## 🔌 API REST

### Endpoints Principais

#### Posts
- `GET /api/posts.php` - Listar todos
- `GET /api/posts.php?id={id}` - Buscar por ID
- `POST /api/posts.php` - Criar novo
- `PUT /api/posts.php?id={id}` - Atualizar
- `DELETE /api/posts.php?id={id}` - Deletar

#### Categorias
- `GET /api/categorias.php` - Listar todas
- `POST /api/categorias.php` - Criar nova
- `PUT /api/categorias.php?id={id}` - Atualizar
- `DELETE /api/categorias.php?id={id}` - Deletar

#### Usuários
- `GET /api/usuarios.php` - Listar todos
- `POST /api/usuarios.php` - Criar novo
- `PUT /api/usuarios.php?id={id}` - Atualizar
- `DELETE /api/usuarios.php?id={id}` - Deletar

#### Comentários
- `GET /api/comentários.php` - Listar todos
- `POST /api/comentários.php` - Criar novo
- `PUT /api/comentários.php?id={id}` - Atualizar
- `DELETE /api/comentários.php?id={id}` - Deletar

#### Perfis de Autor
- `GET /api/perfil_autor.php` - Listar todos
- `POST /api/perfil_autor.php` - Criar novo
- `PUT /api/perfil_autor.php?id={id}` - Atualizar
- `DELETE /api/perfil_autor.php?id={id}` - Deletar

### Exemplo de Uso com JavaScript

```javascript
// GET - Listar posts
fetch('./api/posts.php')
  .then(r => r.json())
  .then(posts => console.log(posts));

// POST - Criar post
const formData = new FormData();
formData.append('titulo', 'Meu Post');
formData.append('conteudo', 'Conteúdo do post...');
formData.append('autor_id', 1);
formData.append('categoria_id', 1);

fetch('./api/posts.php', {
  method: 'POST',
  body: formData
}).then(r => r.json()).then(data => console.log(data));

// PUT - Atualizar post
const params = new URLSearchParams({
  titulo: 'Título Atualizado',
  conteudo: 'Novo conteúdo...'
});

fetch('./api/posts.php?id=1', {
  method: 'PUT',
  body: params
}).then(r => r.json()).then(data => console.log(data));

// DELETE - Deletar post
fetch('./api/posts.php?id=1', { 
  method: 'DELETE' 
}).then(r => r.json()).then(data => console.log(data));
```

## 📁 Estrutura do Projeto

```
postblog_web2/
├── public/
│   ├── index.html              # Página inicial
│   ├── posts.html              # Gerenciamento de posts
│   ├── categorias.html         # Gerenciamento de categorias
│   ├── usuarios.html           # Gerenciamento de usuários
│   ├── comentarios.html        # Gerenciamento de comentários
│   ├── perfis.html             # Gerenciamento de perfis
│   ├── api-docs.html           # Documentação da API
│   └── api/
│       ├── posts.php           # API de posts
│       ├── categorias.php      # API de categorias
│       ├── usuarios.php        # API de usuários
│       ├── comentários.php     # API de comentários
│       └── perfil_autor.php    # API de perfis
├── src/
│   ├── database.php            # Conexão com banco
│   └── models/
│       ├── Post.php            # Model de Post
│       ├── Categoria.php       # Model de Categoria
│       ├── Usuario.php         # Model de Usuario
│       ├── Comentario.php      # Model de Comentario
│       └── PerfilAutor.php     # Model de PerfilAutor
├── sql/
│   └── blogpost.sql            # Script do banco de dados
└── README.md
```

## 🎨 Tecnologias Utilizadas

- **Backend**: PHP 8.1+
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3.2
- **Ícones**: Bootstrap Icons 1.11.1
- **Arquitetura**: REST API

## ✨ Funcionalidades

### Posts
- Criar, editar, listar e deletar posts
- Upload de imagens
- Associação com autores e categorias
- Ordenação por data

### Categorias
- CRUD completo de categorias
- Validação de nomes únicos

### Usuários
- Registro de usuários
- Senha criptografada com `password_hash()`
- Campos: username, nome, sobrenome, email

### Comentários
- Sistema completo de comentários
- Associação com posts e autores
- Moderação facilitada

### Perfis de Autor
- Biografia personalizada
- Foto de perfil
- Redes sociais (formato JSON)

## 🔒 Segurança

- Senhas criptografadas com `password_hash()`
- Prepared Statements para prevenir SQL Injection
- Headers CORS configurados
- Validação de dados no backend

## 📝 Licença

Este projeto é de código aberto e está disponível para uso educacional.

## 👤 Autor

**Micael Rosario**
- GitHub: [@micaelrosario](https://github.com/micaelrosario)

## 🤝 Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues e pull requests.

---

**Desenvolvido com ❤️ usando PHP, MySQL e Bootstrap**