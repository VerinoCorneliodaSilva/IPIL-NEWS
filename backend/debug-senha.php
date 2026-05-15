<?php
/**
 * TESTE DETALHADO DE SENHA
 * Para diagnosticar o erro "Senha incorreta"
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Debug Senha - IPIL News</title>";
echo "<style>";
echo "body { font-family: 'Courier New', monospace; margin: 20px; background: #1e1e1e; color: #ddd; }";
echo ".container { max-width: 800px; margin: 0 auto; }";
echo ".box { background: #2d2d2d; padding: 15px; margin: 10px 0; border-left: 4px solid #FF5722; border-radius: 3px; }";
echo ".ok { border-left-color: #4CAF50; }";
echo ".erro { border-left-color: #f44336; }";
echo ".info { border-left-color: #2196F3; }";
echo "h1 { color: #FF5722; }";
echo "h2 { color: #FFC107; margin-top: 20px; }";
echo "input { padding: 8px; margin: 5px 0; width: 100%; max-width: 400px; background: #3d3d3d; color: #fff; border: 1px solid #FF5722; }";
echo "button { background: #FF5722; color: white; border: none; padding: 10px 20px; cursor: pointer; margin: 10px 0; border-radius: 3px; }";
echo "button:hover { background: #E64A19; }";
echo ".senha-info { background: #3d3d3d; padding: 10px; margin: 10px 0; border-radius: 3px; font-size: 12px; word-break: break-all; }";
echo "code { background: #1e1e1e; padding: 2px 5px; border-radius: 2px; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 Debug de Autenticação - IPIL News</h1>";

// ✅ Teste 1: Listar todos os utilizadores
echo "<div class='box info'>";
echo "<h2>1️⃣ Utilizadores Registados</h2>";
try {
    require_once __DIR__ . '/config.php';
    $pdo = getDB();
    
    $stmt = $pdo->query("
        SELECT u.id, u.email, u.nome, u.ativo
        FROM users u
        ORDER BY u.id ASC
    ");
    
    $users = $stmt->fetchAll();
    echo "<p>Total: <strong>" . count($users) . "</strong> utilizadores</p>";
    
    if (count($users) > 0) {
        echo "<table style='width:100%; font-size: 12px;'>";
        echo "<tr style='background: #3d3d3d;'><th style='padding: 5px;'>ID</th><th style='padding: 5px;'>Email</th><th style='padding: 5px;'>Nome</th><th style='padding: 5px;'>Ativo</th></tr>";
        foreach ($users as $u) {
            echo "<tr style='border-bottom: 1px solid #3d3d3d;'>";
            echo "<td style='padding: 5px;'>" . $u['id'] . "</td>";
            echo "<td style='padding: 5px;'><code>" . htmlspecialchars($u['email']) . "</code></td>";
            echo "<td style='padding: 5px;'>" . htmlspecialchars($u['nome']) . "</td>";
            echo "<td style='padding: 5px;'>" . ($u['ativo'] ? '✓' : '✗') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='erro'><strong>❌ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ✅ Teste 2: Testar senha
echo "<div class='box info'>";
echo "<h2>2️⃣ Testar Senha</h2>";
echo "<form method='POST'>";
echo "<label>Email:</label><br>";
echo "<input type='email' name='debug_email' placeholder='admin@ipil.ao' value='" . htmlspecialchars($_POST['debug_email'] ?? '') . "' required>";
echo "<br><label>Senha:</label><br>";
echo "<input type='password' name='debug_senha' placeholder='Digite a senha' required>";
echo "<br><button type='submit' name='debug_test' value='1'>🔍 Testar</button>";
echo "</form>";

if (isset($_POST['debug_test'])) {
    $debugEmail = $_POST['debug_email'] ?? '';
    $debugSenha = $_POST['debug_senha'] ?? '';
    
    echo "<div class='box ok'>";
    echo "<h3>📊 Resultado</h3>";
    
    try {
        require_once __DIR__ . '/config.php';
        $pdo = getDB();
        
        $stmt = $pdo->prepare("
            SELECT u.id, u.email, u.nome, u.senha_hash, u.ativo, r.nome AS role
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
        ");
        
        $stmt->execute([$debugEmail]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "<div class='erro'><strong>❌ Utilizador não encontrado</strong></div>";
            echo "<p>Email procurado: <code>" . htmlspecialchars($debugEmail) . "</code></p>";
        } else {
            echo "<p><strong>✓ Utilizador encontrado:</strong></p>";
            echo "<p><strong>Nome:</strong> " . htmlspecialchars($user['nome']) . "</p>";
            echo "<p><strong>Email:</strong> <code>" . htmlspecialchars($user['email']) . "</code></p>";
            echo "<p><strong>Role:</strong> " . ($user['role'] ?? 'N/A') . "</p>";
            echo "<p><strong>Ativo:</strong> " . ($user['ativo'] ? '✓ Sim' : '❌ Não') . "</p>";
            
            echo "<hr style='border: 0; border-top: 1px solid #3d3d3d; margin: 15px 0;'>";
            echo "<p><strong>Hash guardado na BD:</strong></p>";
            echo "<div class='senha-info'>" . htmlspecialchars($user['senha_hash']) . "</div>";
            
            echo "<hr style='border: 0; border-top: 1px solid #3d3d3d; margin: 15px 0;'>";
            echo "<p><strong>Testando senha...</strong></p>";
            echo "<p>Senha digitada: <code>" . htmlspecialchars($debugSenha) . "</code></p>";
            echo "<p>Comprimento: " . strlen($debugSenha) . " caracteres</p>";
            
            // Teste direto com password_verify
            $isValid = password_verify($debugSenha, $user['senha_hash']);
            
            if ($isValid) {
                echo "<div class='box ok'>";
                echo "<h3>✅ SENHA CORRECTA!</h3>";
                echo "<p>A senha deveria funcionar no login. Se ainda houver erro, pode ser um problema no PHP.</p>";
                echo "</div>";
            } else {
                echo "<div class='box erro'>";
                echo "<h3>❌ SENHA INCORRETA</h3>";
                echo "<p>A senha digitada não corresponde ao hash guardado.</p>";
                echo "<p><strong>Dicas:</strong></p>";
                echo "<ul>";
                echo "<li>Verifique se tem CAPS LOCK ativado</li>";
                echo "<li>Verifique se tem espaços no início ou fim da senha</li>";
                echo "<li>Verifique se a sua senha tem caracteres especiais</li>";
                echo "<li>Tente copiar e colar a senha do seu gestor de senhas</li>";
                echo "</ul>";
                echo "</div>";
            }
            
            // Info extra
            echo "<hr style='border: 0; border-top: 1px solid #3d3d3d; margin: 15px 0;'>";
            echo "<p><strong>Informações Técnicas:</strong></p>";
            echo "<div class='senha-info'>";
            echo "Tipo de Hash: BCRYPT (\$2y\$)<br>";
            echo "Começa com: " . substr($user['senha_hash'], 0, 10) . "<br>";
            echo "Tamanho do Hash: " . strlen($user['senha_hash']) . " caracteres<br>";
            echo "password_verify() retornou: " . ($isValid ? 'true' : 'false');
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='erro'><strong>❌ Erro no servidor:</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>
