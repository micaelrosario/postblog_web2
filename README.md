# 📰 BlogPost - Sistema de Gerenciamento de Blog

Sistema completo de gerenciamento de blog com API REST (GET, POST, PUT, DELETE) e interface renderizada no backend (PHP) com Bootstrap 5.

## 🚀 Características

- ✅ **API REST completa** com suporte a GET, POST, PUT e DELETE
- ✅ **Interface moderna** com Bootstrap 5 e Bootstrap Icons
- ✅ **CRUD completo** para Posts, Categorias, Usuários, Comentários e Perfis de Autor
- ✅ **Headers CORS** configurados
- ✅ **Respostas em JSON** padronizadas
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
  - Edite `config/database.php`
   - Ajuste as credenciais do banco de dados

4. **Inicie o XAMPP**:
   - Inicie o Apache
   - Inicie o MySQL

## 🎯 Como Usar

### Acesso ao Sistema

1. Abra o navegador e acesse: `http://localhost/postblog_web2/`
2. O `index.php` é o **front controller**: recebe a rota (`?url=` via `.htaccess`) e chama a **classe da rota** + **método HTTP**

### Rotas Disponíveis (Interface PHP)

| Rota | Descrição |
|------|-----------|
| `/posts` | Gerenciar posts |
| `/categorias` | Gerenciar categorias |
| `/usuarios` | Gerenciar usuários |
| `/comentarios` | Gerenciar comentários |
| `/perfis` | Gerenciar perfis |
| `/login` | Login |
| `/cadastro` | Criar usuário (público) |
| `/logout` | Logout |

## 🔌 API REST

### ✅ Roteador Central (rotas no index)

O `index.php` da raiz é o centralizador. Ele roteia:

- **Interface (HTML)**: `/posts`, `/categorias`, ... → classes em `classes/`
- **API (JSON)**: `/api/{recurso}` (recomendado) e também `?resource=...` (compatibilidade)

O roteamento da API fica na classe `Api` em `classes/Api.php`.

### 🔐 Autenticação (API)

- `POST /api/login` com `username` e `senha` (JSON ou form-urlencoded) → cria sessão
- `POST /api/logout` → encerra sessão
- Requisições `POST/PUT/DELETE` para os recursos exigem autenticação (retornam `401` se não autenticado)

Exemplos (API):

- `GET /api/posts`
- `GET /api/posts/1`
- `POST /api/usuarios`
- `PUT /api/categorias/1`
- `DELETE /api/comentarios/1`

Compatibilidade (mantido):

- `GET /?resource=posts`
- `GET /?resource=posts&id=1`
- `POST /?resource=usuarios`
- `PUT /?resource=categorias&id=1`
- `DELETE /?resource=comentarios&id=1`

### Endpoints Principais

#### Posts
- `GET /?resource=posts` - Listar todos
- `GET /?resource=posts&id={id}` - Buscar por ID
- `POST /?resource=posts` - Criar novo
- `PUT /?resource=posts&id={id}` - Atualizar
- `DELETE /?resource=posts&id={id}` - Deletar

#### Categorias
- `GET /?resource=categorias` - Listar todas
- `GET /?resource=categorias&id={id}` - Buscar por ID
- `POST /?resource=categorias` - Criar nova
- `PUT /?resource=categorias&id={id}` - Atualizar
- `DELETE /?resource=categorias&id={id}` - Deletar

#### Usuários
- `GET /?resource=usuarios` - Listar todos
- `GET /?resource=usuarios&id={id}` - Buscar por ID
- `POST /?resource=usuarios` - Criar novo
- `PUT /?resource=usuarios&id={id}` - Atualizar
- `DELETE /?resource=usuarios&id={id}` - Deletar

#### Comentários
- `GET /?resource=comentarios` - Listar todos
- `GET /?resource=comentarios&id={id}` - Buscar por ID
- `POST /?resource=comentarios` - Criar novo
- `PUT /?resource=comentarios&id={id}` - Atualizar
- `DELETE /?resource=comentarios&id={id}` - Deletar

#### Perfis de Autor
- `GET /?resource=perfil_autor` - Listar todos
- `GET /?resource=perfil_autor&id={id}` - Buscar por ID
- `POST /?resource=perfil_autor` - Criar novo
- `PUT /?resource=perfil_autor&id={id}` - Atualizar
- `DELETE /?resource=perfil_autor&id={id}` - Deletar

### Exemplo de Uso com JavaScript

```javascript
const API_URL = './index.php?resource=posts';

// GET - Listar posts
fetch(API_URL)
  .then(r => r.json())
  .then(posts => console.log(posts));

// POST - Criar post
const formData = new FormData();
formData.append('titulo', 'Meu Post');
formData.append('conteudo', 'Conteúdo do post...');
formData.append('autor_id', 1);
formData.append('categoria_id', 1);

fetch(API_URL, {
  method: 'POST',
  body: formData
}).then(r => r.json()).then(data => console.log(data));

// PUT - Atualizar post
const params = new URLSearchParams({
  titulo: 'Título Atualizado',
  conteudo: 'Novo conteúdo...'
});

// PUT - Atualizar post
fetch(`${API_URL}&id=1`, {
  method: 'PUT',
  body: params
}).then(r => r.json()).then(data => console.log(data));

// DELETE - Deletar post
fetch(`${API_URL}&id=1`, {
  method: 'DELETE'
}).then(r => r.json()).then(data => console.log(data));
```

## 📁 Estrutura do Projeto

```
postblog_web2/
├── index.php                   # Front controller (roteador)
├── .htaccess                   # Rewrite: index.php?url=...
├── router.php                  # Router p/ php -S (desenvolvimento)
├── classes/
│   ├── Posts.php               # Rota /posts (HTML)
│   ├── Categorias.php          # Rota /categorias (HTML)
│   ├── Usuarios.php            # Rota /usuarios (HTML)
│   ├── Comentarios.php         # Rota /comentarios (HTML)
│   ├── Perfis.php              # Rota /perfis (HTML)
│   ├── Api.php                 # Rota /api/{recurso} (JSON)
│   └── Auth.php                # Rotas /login, /cadastro, /logout (HTML)
├── config/
│   └── database.php            # Config/conexão com banco
├── views/
│   ├── templates.php           # topo()/rodape() e helpers
│   ├── auth.php                # Views de login/cadastro
│   ├── posts.php               # View de posts
│   ├── categorias.php          # View de categorias
│   ├── usuarios.php            # View de usuários
│   ├── comentarios.php         # View de comentários
│   └── perfis.php              # View de perfis
├── src/
│   └── models/                 # Models (CRUD)
├── sql/
│   └── blogpost.sql            # Script do banco de dados
└── README.md
```

## 🎨 Tecnologias Utilizadas

- **Backend**: PHP 8.1+
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.3.2
- **Arquitetura**: Index centralizador + Models + API REST

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