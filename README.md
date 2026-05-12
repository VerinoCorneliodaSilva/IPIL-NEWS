# IPIL News Portal
**Instituto Politécnico Industrial de Luanda**

Portal de notícias interno para alunos, professores, diretores e administradores do IPIL.

---

## Estrutura de Pastas

```
ipil-news/
├── database/
│   └── schema.sql          ← Script SQL completo (tabelas + seed)
├── backend/
│   ├── config.php          ← Conexão PDO, sessão, helpers
│   ├── auth.php            ← Login, registo, logout
│   ├── news.php            ← CRUD de notícias
│   └── validation.php      ← Gestão de códigos IPIL (só Admin)
├── frontend/
│   ├── login.html          ← Página de login
│   ├── registar.html       ← Página de cadastro com validação IPIL
│   ├── index.html          ← Feed de notícias (utilizadores autenticados)
│   ├── perfil.html         ← Perfil do utilizador
│   └── admin.html          ← Painel de administração
└── assets/
    ├── css/
    │   └── style.css       ← Estilos base (paleta IPIL)
    ├── js/
    │   └── main.js         ← Funções partilhadas (auth, logout, utilitários)
    └── img/                ← Imagens e logótipo
```

---

## Instalação

### 1. Requisitos
- PHP 8.0 ou superior (com extensões PDO e PDO_MySQL)
- MySQL 5.7 / MariaDB 10.3 ou superior
- Servidor web: Apache (com mod_rewrite) ou Nginx

### 2. Base de Dados
```sql
-- No MySQL Workbench, phpMyAdmin ou terminal:
source /caminho/para/ipil-news/database/schema.sql;
```

### 3. Configuração
Editar `backend/config.php` com as credenciais do servidor:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ipil_news');
define('DB_USER', 'root');
define('DB_PASS', 'a_sua_senha');
```

### 4. Configuração do Servidor Web

**Apache** — criar `.htaccess` na raiz do projecto:
```apache
Options -Indexes
```

**Nginx** — garantir que `php-fpm` está activo e os ficheiros PHP são processados.

### 5. Primeiro Acesso — Admin
| Campo | Valor |
|-------|-------|
| E-mail | `admin@ipil.ao` |
| Senha  | `Admin@IPIL2024` |

> **IMPORTANTE:** Altere a senha do admin imediatamente após o primeiro login (via base de dados ou adicione um formulário de alteração de senha).

---

## Fluxo de Validação IPIL

```
Admin cria código (ex: 20240005 para aluno)
        ↓
Admin distribui o código ao aluno (papel, email, etc.)
        ↓
Aluno acede a registar.html
        ↓
Aluno preenche: nome, email, role=aluno, código=20240005, senha
        ↓
Backend verifica: código existe + role correcto + não usado?
        ↓ SIM                          ↓ NÃO
Conta criada + código marcado        Erro: código inválido
como "usado"
```

---

## Roles e Permissões

| Role      | Ver Feed | Registar | Criar/Editar Notícias | Gerir Códigos |
|-----------|----------|----------|----------------------|---------------|
| Admin     | ✔        | N/A      | ✔                    | ✔             |
| Diretor   | ✔        | ✔        | ✗                    | ✗             |
| Professor | ✔        | ✔        | ✗                    | ✗             |
| Aluno     | ✔        | ✔        | ✗                    | ✗             |

---

## Segurança Implementada

| Ameaça          | Protecção |
|----------------|-----------|
| SQL Injection   | PDO Prepared Statements em todas as queries |
| XSS             | `htmlspecialchars()` no backend; `escaparHTML()` no frontend |
| CSRF            | Cookie `SameSite=Strict` |
| Sessão          | Cookie `HttpOnly` (inacessível por JavaScript) |
| Senhas          | `password_hash()` com bcrypt, custo 12 |
| Acesso por Role | `requireRole()` em cada endpoint sensível |
| Registo indevido| Código de validação IPIL obrigatório |

---

## Paleta de Cores

| Nome    | Código    | Uso |
|---------|-----------|-----|
| Amarelo | `#FFC107` | Botões secundários, destaques, badge aluno |
| Laranja | `#FF5722` | Cabeçalho, botões primários, links |
| Branco  | `#FFFFFF` | Fundo de cards e formulários |
