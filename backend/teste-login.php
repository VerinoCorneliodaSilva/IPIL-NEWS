<?php
/**
 * FICHEIRO DE TESTE PARA DIAGNÓSTICO
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/teste-login.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Teste de Login - IPIL News</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; background: #f5f5f5; }";
echo ".teste { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo ".ok { border-left: 4px solid green; }";
echo ".erro { border-left: 4px solid red; }";
echo ".info { border-left: 4px solid blue; }";
echo "h1 { color: #FF5722; }";
echo "pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }";
echo "</style></head><body>";
echo "<h1>🔍 Diagnóstico de Login - IPIL News Portal</h1>";

// ✅ Teste 1: Conexão com a base de dados
echo "<div class='teste'>";
echo "<h2>✅ Teste 1: Conexão com a Base de Dados</h2>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ipil_news;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<div class='ok'><strong>✓ Conectado com sucesso!</strong></div>";
    
    // Verificar tabelas
    $tables = ['users', 'roles', 'validation_codes'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        echo "<p>✓ Tabela <strong>$table</strong> existe</p>";
    }
} catch (PDOException $e) {
    echo "<div class='erro'><strong>✗ Erro de Conexão:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}
echo "</div>";

// ✅ Teste 2: Verificar se o ficheiro auth.php existe
echo "<div class='teste'>";
echo "<h2>✅ Teste 2: Ficheiro auth.php</h2>";
$authFile = __DIR__ . '/auth.php';
if (file_exists($authFile)) {
    echo "<div class='ok'><strong>✓ Ficheiro auth.php existe</strong></div>";
    echo "<p>Tamanho: " . filesize($authFile) . " bytes</p>";
} else {
    echo "<div class='erro'><strong>✗ Ficheiro auth.php não encontrado</strong></div>";
}
echo "</div>";

// ✅ Teste 3: Verificar dados de teste
echo "<div class='teste'>";
echo "<h2>✅ Teste 3: Dados de Utilizadores</h2>";
try {
    $stmt = $pdo->query("SELECT id, nome, email, ativo FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<div class='ok'><strong>✓ Utilizadores encontrados:</strong></div>";
        echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . ($user['ativo'] ? '✓ Sim' : '✗ Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'><strong>ℹ Nenhum utilizador registado ainda</strong></div>";
    }
} catch (Exception $e) {
    echo "<div class='erro'><strong>✗ Erro ao consultar utilizadores:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}
echo "</div>";

// ✅ Teste 4: Verificar sessão
echo "<div class='teste'>";
echo "<h2>✅ Teste 4: Sessão PHP</h2>";
echo "<p>Session ID: <strong>" . session_id() . "</strong></p>";
echo "<p>Session Status: <strong>" . (session_status() === PHP_SESSION_ACTIVE ? 'Ativa' : 'Inativa') . "</strong></p>";
echo "</div>";

// ✅ Teste 5: Instruções
echo "<div class='teste info'>";
echo "<h2>📋 Instruções para Testar</h2>";
echo "<ol>";
echo "<li>Aceda a: <strong>http://localhost/TLP-Projetos-corrigido/ipil-news/frontend/login.html</strong></li>";
echo "<li>Introduza um e-mail de um utilizador registado</li>";
echo "<li>Introduza a senha correspondente</li>";
echo "<li>Se ver <strong>'Preenchimento Automático'</strong> - FOI CORRIGIDO ✓</li>";
echo "<li>Se a autenticação falhar, verifique acima os testes</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
