<?php
/**
 * DIAGNÓSTICO COMPLETO DE SENHA
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/diagnostico-senha.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Diagnóstico de Senha - IPIL News</title>";
echo "<style>";
echo "body { font-family: 'Courier New', monospace; margin: 20px; background: #1e1e1e; color: #ddd; }";
echo ".container { max-width: 1200px; margin: 0 auto; }";
echo "h1 { color: #FF5722; }";
echo "h2 { color: #FFC107; border-bottom: 2px solid #FF5722; padding-bottom: 10px; margin-top: 30px; }";
echo ".box { background: #2d2d2d; padding: 15px; margin: 10px 0; border-left: 4px solid #FF5722; border-radius: 3px; }";
echo ".ok { border-left-color: #4CAF50; }";
echo ".erro { border-left-color: #f44336; }";
echo ".info { border-left-color: #2196F3; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "th, td { padding: 10px; text-align: left; border-bottom: 1px solid #3d3d3d; }";
echo "th { background: #FF5722; color: white; }";
echo ".hash { background: #1e1e1e; padding: 10px; margin: 10px 0; border-radius: 3px; word-break: break-all; font-size: 11px; }";
echo "input { padding: 8px; margin: 5px 0; width: 100%; max-width: 400px; background: #3d3d3d; color: #fff; border: 1px solid #FF5722; }";
echo "button { background: #FF5722; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 3px; margin: 10px 0; }";
echo "button:hover { background: #E64A19; }";
echo "code { background: #1e1e1e; padding: 3px 8px; border-radius: 2px; }";
echo ".sucesso { color: #4CAF50; }";
echo ".erro-texto { color: #f44336; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔍 Diagnóstico Completo de Autenticação</h1>";

require_once __DIR__ . '/config.php';
$pdo = getDB();

// ✅ Seção 1: Listar todos os utilizadores
echo "<h2>1️⃣ Todos os Utilizadores na BD</h2>";
echo "<div class='box info'>";

try {
    $stmt = $pdo->query("
        SELECT u.id, u.nome, u.email, u.senha_hash, u.ativo, r.nome as role
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        ORDER BY u.id ASC
    ");
    
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th><th>Role</th><th>Hash (primeiros 30 chars)</th></tr>";
        foreach ($users as $user) {
            $hashPreview = substr($user['senha_hash'], 0, 30) . "...";
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nome']) . "</td>";
            echo "<td><code>" . htmlspecialchars($user['email']) . "</code></td>";
            echo "<td>" . ($user['ativo'] ? '✓' : '✗') . "</td>";
            echo "<td>" . ($user['role'] ?? '-') . "</td>";
            echo "<td><code>" . $hashPreview . "</code></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p>✅ Total: <strong>" . count($users) . "</strong> utilizadores</p>";
    } else {
        echo "<div class='erro'>❌ Nenhum utilizador encontrado!</div>";
    }
} catch (Exception $e) {
    echo "<div class='erro'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>";

// ✅ Seção 2: Testar password_verify com o Admin
echo "<h2>2️⃣ Teste com Admin (Padrão)</h2>";
echo "<div class='box'>";

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.nome, u.email, u.senha_hash, u.ativo, r.nome as role
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        WHERE u.email = 'admin@ipil.ao'
    ");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Nome:</strong> " . htmlspecialchars($admin['nome']) . "</p>";
        echo "<p><strong>Ativo:</strong> " . ($admin['ativo'] ? 'Sim ✓' : 'Não ✗') . "</p>";
        echo "<p><strong>Hash Completo:</strong></p>";
        echo "<div class='hash'>" . htmlspecialchars($admin['senha_hash']) . "</div>";
        
        echo "<p><strong>Testando Senhas:</strong></p>";
        
        // Testar várias senhas
        $senhas_teste = [
            'Admin@IPIL2024',
            'admin@ipil2024',
            'Admin@IPIL2024 ',
            ' Admin@IPIL2024',
            'admin',
            'IPIL2024'
        ];
        
        echo "<table>";
        echo "<tr><th>Senha Testada</th><th>password_verify()</th><th>Resultado</th></tr>";
        
        foreach ($senhas_teste as $senha) {
            $resultado = password_verify($senha, $admin['senha_hash']);
            $classe = $resultado ? 'sucesso' : 'erro-texto';
            echo "<tr>";
            echo "<td><code>'" . htmlspecialchars($senha) . "'</code> (" . strlen($senha) . " chars)</td>";
            echo "<td class='" . $classe . "'>" . ($resultado ? 'TRUE' : 'FALSE') . "</td>";
            echo "<td>" . ($resultado ? '✅ CORRECTA' : '❌ INCORRETA') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } else {
        echo "<div class='erro'>❌ Admin não encontrado na BD!</div>";
    }
} catch (Exception $e) {
    echo "<div class='erro'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>";

// ✅ Seção 3: Testar com qualquer utilizador
echo "<h2>3️⃣ Teste com Qualquer Utilizador</h2>";
echo "<div class='box'>";
echo "<form method='POST'>";
echo "<label>Email:</label>";
echo "<input type='email' name='diag_email' placeholder='admin@ipil.ao' value='" . htmlspecialchars($_POST['diag_email'] ?? '') . "' required>";
echo "<br><label>Senha para Testar:</label>";
echo "<input type='text' name='diag_senha' placeholder='Digite a senha' value='" . htmlspecialchars($_POST['diag_senha'] ?? '') . "' required>";
echo "<br><button type='submit' name='diag_test' value='1'>🔍 Testar</button>";
echo "</form>";

if (isset($_POST['diag_test'])) {
    $diagEmail = $_POST['diag_email'] ?? '';
    $diagSenha = $_POST['diag_senha'] ?? '';
    
    echo "<hr style='border: 0; border-top: 1px solid #3d3d3d; margin: 20px 0;'>";
    echo "<h3>Resultado:</h3>";
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.id, u.nome, u.email, u.senha_hash, u.ativo, r.nome as role
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            WHERE u.email = ?
        ");
        $stmt->execute([$diagEmail]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "<div class='box erro'>";
            echo "❌ <strong>Utilizador não encontrado</strong>";
            echo "</div>";
        } else {
            echo "<div class='box ok'>";
            echo "✅ <strong>Utilizador encontrado:</strong><br>";
            echo "ID: " . $user['id'] . "<br>";
            echo "Nome: " . htmlspecialchars($user['nome']) . "<br>";
            echo "Email: " . htmlspecialchars($user['email']) . "<br>";
            echo "Role: " . ($user['role'] ?? '-') . "<br>";
            echo "Ativo: " . ($user['ativo'] ? 'Sim' : 'Não') . "<br>";
            echo "</div>";
            
            if (!$user['ativo']) {
                echo "<div class='box erro'>";
                echo "❌ <strong>Conta desativada!</strong>";
                echo "</div>";
            } else {
                echo "<div class='box'>";
                echo "<p><strong>Hash na BD:</strong></p>";
                echo "<div class='hash'>" . htmlspecialchars($user['senha_hash']) . "</div>";
                
                echo "<p><strong>Senha Testada:</strong></p>";
                echo "<div class='hash'>" . htmlspecialchars($diagSenha) . "</div>";
                echo "<p>Comprimento: " . strlen($diagSenha) . " caracteres</p>";
                echo "<p>Bytes: " . strlen($diagSenha) . "</p>";
                
                // Testar password_verify
                $resultado = password_verify($diagSenha, $user['senha_hash']);
                
                echo "<hr style='border: 0; border-top: 1px solid #3d3d3d; margin: 15px 0;'>";
                echo "<p><strong>Resultado de password_verify():</strong></p>";
                
                if ($resultado) {
                    echo "<div class='box ok'>";
                    echo "<h3 class='sucesso'>✅ SENHA CORRECTA!</h3>";
                    echo "<p>A autenticação deveria funcionar normalmente.</p>";
                    echo "</div>";
                } else {
                    echo "<div class='box erro'>";
                    echo "<h3 class='erro-texto'>❌ SENHA INCORRETA</h3>";
                    
                    // Tentar outras variações
                    echo "<p><strong>Testando variações:</strong></p>";
                    $variacoes = [
                        'com espaço antes' => ' ' . $diagSenha,
                        'com espaço depois' => $diagSenha . ' ',
                        'trimmed' => trim($diagSenha),
                        'maiúscula' => strtoupper($diagSenha),
                        'minúscula' => strtolower($diagSenha),
                    ];
                    
                    echo "<table>";
                    echo "<tr><th>Variação</th><th>Resultado</th></tr>";
                    foreach ($variacoes as $desc => $var) {
                        $res = password_verify($var, $user['senha_hash']);
                        echo "<tr>";
                        echo "<td>" . $desc . "</td>";
                        echo "<td class='" . ($res ? 'sucesso' : 'erro-texto') . "'>" . ($res ? '✅ TRUE' : '❌ FALSE') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    echo "</div>";
                    
                    // Info técnica
                    echo "<div class='box info'>";
                    echo "<p><strong>Informações Técnicas:</strong></p>";
                    echo "<p>Tipo de Hash: " . (strpos($user['senha_hash'], '$2') === 0 ? 'BCRYPT ✓' : 'OUTRO ⚠️') . "</p>";
                    echo "<p>Começa com: " . substr($user['senha_hash'], 0, 20) . "</p>";
                    echo "<p>Tamanho: " . strlen($user['senha_hash']) . " caracteres</p>";
                    echo "<p>Versão PHP: " . phpversion() . "</p>";
                    echo "</div>";
                }
                
                echo "</div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='box erro'>";
        echo "❌ <strong>Erro:</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

echo "</div>";

// ✅ Seção 4: Regenerar hash para teste
echo "<h2>4️⃣ Regenerar Hash de Teste</h2>";
echo "<div class='box info'>";
echo "<form method='POST'>";
echo "<label>Senha para Gerar Hash:</label>";
echo "<input type='text' name='nova_senha' placeholder='Digite uma senha' value='" . htmlspecialchars($_POST['nova_senha'] ?? '') . "' required>";
echo "<br><button type='submit' name='gerar_hash' value='1'>🔐 Gerar Hash</button>";
echo "</form>";

if (isset($_POST['gerar_hash'])) {
    $novaSenha = $_POST['nova_senha'] ?? '';
    $novoHash = password_hash($novaSenha, PASSWORD_BCRYPT);
    
    echo "<hr style='border: 0; border-top: 1px solid #3d3d3d; margin: 20px 0;'>";
    echo "<p><strong>Senha:</strong> <code>" . htmlspecialchars($novaSenha) . "</code></p>";
    echo "<p><strong>Hash Gerado:</strong></p>";
    echo "<div class='hash'>" . htmlspecialchars($novoHash) . "</div>";
    
    // Verificar se password_verify funciona com o novo hash
    $testVerify = password_verify($novaSenha, $novoHash);
    echo "<p><strong>password_verify() com o novo hash:</strong></p>";
    echo "<p class='" . ($testVerify ? 'sucesso' : 'erro-texto') . "'>";
    echo ($testVerify ? '✅ TRUE (Funciona corretamente)' : '❌ FALSE (Erro!)');
    echo "</p>";
}

echo "</div>";

echo "</div>";
echo "</body></html>";
?>
