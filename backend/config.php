<?php
/**
 * config.php
 * Configuração central da aplicação IPIL News Portal
 */

// ------------------------------------------------------------
// CONFIGURAÇÕES DO BANCO DE DADOS
// ------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'ipil_news');
define('DB_USER', 'root');         // Alterar para o utilizador do servidor
define('DB_PASS', '');             // Alterar para a senha do servidor
define('DB_CHARSET', 'utf8mb4');

// ------------------------------------------------------------
// CONFIGURAÇÕES DA SESSÃO
// Cookies HttpOnly impedem acesso via JavaScript (segurança XSS)
// ------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,           // Sessão expira ao fechar o browser
    'path'     => '/',
    'secure'   => false,       // Mudar para TRUE em produção (HTTPS)
    'httponly' => true,        // JavaScript NÃO pode aceder ao cookie
    'samesite' => 'Strict',    // Protecção CSRF
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// CONEXÃO PDO SINGLETON
// PDO + prepared statements previnem SQL Injection
// ------------------------------------------------------------
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

        $opcoes = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Prepared statements reais
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
        } catch (PDOException $e) {
            // Em produção, nunca mostrar detalhes do erro ao utilizador
            error_log('Erro de conexão BD: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['erro' => 'Erro interno do servidor. Tente mais tarde.']));
        }
    }

    return $pdo;
}

// ------------------------------------------------------------
// HELPER: Resposta JSON padronizada
// ------------------------------------------------------------
function jsonResponse(bool $sucesso, string $mensagem, array $dados = []): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso'   => $sucesso,
        'mensagem'  => $mensagem,
        'dados'     => $dados,
    ]);
    exit;
}

// ------------------------------------------------------------
// HELPER: Sanitizar entrada do utilizador
// Previne XSS ao exibir dados no HTML
// ------------------------------------------------------------
function sanitizar(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ------------------------------------------------------------
// HELPER: Verificar se o utilizador está autenticado
// ------------------------------------------------------------
function autenticado(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ------------------------------------------------------------
// HELPER: Verificar se o utilizador tem determinado role
// Uso: requireRole('admin') ou requireRole(['admin', 'diretor'])
// ------------------------------------------------------------
function requireRole(string|array $roles): void {
    if (!autenticado()) {
        header('Location: ../frontend/login.html');
        exit;
    }

    $rolesArray = is_array($roles) ? $roles : [$roles];

    if (!in_array($_SESSION['role'], $rolesArray, true)) {
        http_response_code(403);
        die('<p>Acesso negado. Não tem permissão para esta área.</p>');
    }
}
