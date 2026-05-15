<?php
/**
 * TESTE DE LOGIN DIRETO
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/teste-auth.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Teste de Autenticação - IPIL News</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; background: #f5f5f5; }";
echo ".teste { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo ".ok { border-left: 4px solid green; }";
echo ".erro { border-left: 4px solid red; }";
echo ".info { border-left: 4px solid blue; }";
echo "h1 { color: #FF5722; }";
echo "pre { background: #f0f0f0; padding: 10px; overflow-x: auto; font-size: 12px; }";
echo "input, button { padding: 8px; margin: 5px 0; width: 300px; }";
echo "button { background: #FF5722; color: white; border: none; cursor: pointer; width: auto; }";
echo "button:hover { background: #E64A19; }";
echo ".resultado { margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }";
echo "</style></head><body>";
echo "<h1>🔍 Teste de Autenticação - IPIL News Portal</h1>";

// ✅ Teste 1: Verificar conexão
echo "<div class='teste'>";
echo "<h2>1️⃣ Verificar Utilizadores na BD</h2>";
try {
    require_once __DIR__ . '/config.php';
    $pdo = getDB();
    
    $stmt = $pdo->query("
        SELECT u.id, u.nome, u.email, u.senha_hash, u.ativo, r.nome AS role
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        LIMIT 10
    ");
    
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<div class='ok'><strong>✓ Utilizadores encontrados:</strong></div>";
        echo "<table border='1' style='width:100%; border-collapse: collapse; font-size: 12px;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th><th>Role</th><th>Hash (primeiros 20 chars)</th></tr>";
        foreach ($users as $user) {
            $hashPreview = substr($user['senha_hash'], 0, 20) . "...";
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . ($user['ativo'] ? '✓' : '✗') . "</td>";
            echo "<td>" . ($user['role'] ?? '-') . "</td>";
            echo "<td><code>" . $hashPreview . "</code></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='erro'><strong>✗ Nenhum utilizador na BD!</strong></div>";
    }
} catch (Exception $e) {
    echo "<div class='erro'><strong>✗ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ✅ Teste 2: Formulário de teste
echo "<div class='teste'>";
echo "<h2>2️⃣ Testar Login Manual</h2>";
echo "<form method='POST' style='margin: 10px 0;'>";
echo "<input type='email' name='test_email' placeholder='Email' value='verinocornelio@gmail.com' required>";
echo "<input type='password' name='test_senha' placeholder='Senha' required>";
echo "<button type='submit' name='test_login' value='1'>🔐 Testar Login</button>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $testEmail = $_POST['test_email'] ?? '';
    $testSenha = $_POST['test_senha'] ?? '';
    
    echo "<div class='resultado'>";
    echo "<h3>📊 Resultado do Teste</h3>";
    
    try {
        require_once __DIR__ . '/config.php';
        $pdo = getDB();
        
        // Buscar utilizador
        $stmt = $pdo->prepare("
            SELECT u.id, u.nome, u.senha_hash, u.ativo, r.nome AS role
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
        ");
        
        $stmt->execute([$testEmail]);
        $user = $stmt->fetch();
        
        echo "<p><strong>Email procurado:</strong> " . htmlspecialchars($testEmail) . "</p>";
        
        if (!$user) {
            echo "<div class='erro'><strong>❌ Utilizador não encontrado</strong></div>";
        } else {
            echo "<div class='ok'><strong>✓ Utilizador encontrado:</strong></div>";
            echo "<pre>";
            echo "ID: " . $user['id'] . "\n";
            echo "Nome: " . $user['nome'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Role: " . ($user['role'] ?? 'N/A') . "\n";
            echo "Ativo: " . ($user['ativo'] ? 'Sim' : 'Não') . "\n";
            echo "Hash (primeiros 30 chars): " . substr($user['senha_hash'], 0, 30) . "...\n";
            echo "</pre>";
            
            // Verificar ativo
            if (!$user['ativo']) {
                echo "<div class='erro'><strong>❌ Conta desativada!</strong></div>";
            } else {
                // Testar senha
                echo "<p><strong>Testando senha...</strong></p>";
                echo "<p>Senha inserida: <code>" . htmlspecialchars($testSenha) . "</code></p>";
                
                if (password_verify($testSenha, $user['senha_hash'])) {
                    echo "<div class='ok'><strong>✓ SENHA CORRECTA!</strong></div>";
                    echo "<p>Login deveria funcionar normalmente.</p>";
                } else {
                    echo "<div class='erro'><strong>❌ SENHA INCORRETA!</strong></div>";
                    echo "<p>A senha não corresponde ao hash guardado.</p>";
                    
                    // Info extra
                    echo "<p style='color: #999; font-size: 12px;'>";
                    echo "Hash do BD começa com: " . substr($user['senha_hash'], 0, 20) . "...<br>";
                    echo "Hash da senha inserida: " . password_hash($testSenha, PASSWORD_BCRYPT) . "<br>";
                    echo "Tipo de hash no BD: " . (strpos($user['senha_hash'], '$2') === 0 ? 'BCRYPT ✓' : 'OUTRO');
                    echo "</p>";
                }
            }
        }
    } catch (Exception $e) {
        echo "<div class='erro'><strong>✗ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    echo "</div>";
}

echo "</div>";

// ✅ Teste 3: Instruções
echo "<div class='teste info'>";
echo "<h2>📋 O que testar:</h2>";
echo "<ul>";
echo "<li><strong>Passo 1:</strong> Refresque a página de login (Ctrl+F5) para limpar cache</li>";
echo "<li><strong>Passo 2:</strong> Os campos devem estar VAZIOS (sem preenchimento automático)</li>";
echo "<li><strong>Passo 3:</strong> Use este formulário acima para testar o login</li>";
echo "<li><strong>Passo 4:</strong> Se a senha estiver correta, o login deveria funcionar</li>";
echo "<li><strong>Passo 5:</strong> Se ainda houver erro, execute <code>http://localhost/TLP-Projetos-corrigido/ipil-news/backend/teste-login.php</code></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
