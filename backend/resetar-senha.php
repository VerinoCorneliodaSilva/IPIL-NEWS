<?php
/**
 * RESETAR SENHA DE UTILIZADOR
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/resetar-senha.php
 * 
 * AVISO: Em produção, isto deve estar protegido!
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Resetar Senha - IPIL News</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; }";
echo ".box { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo ".ok { border-left: 4px solid #4CAF50; }";
echo ".erro { border-left: 4px solid #f44336; }";
echo ".aviso { border-left: 4px solid #FFC107; }";
echo "h1 { color: #FF5722; }";
echo "h2 { color: #333; border-bottom: 2px solid #FF5722; padding-bottom: 10px; }";
echo "input, select { padding: 10px; width: 100%; margin: 10px 0; box-sizing: border-box; font-size: 16px; }";
echo "button { background: #FF5722; color: white; border: none; padding: 12px 20px; cursor: pointer; width: 100%; border-radius: 3px; font-size: 16px; margin: 10px 0; }";
echo "button:hover { background: #E64A19; }";
echo "code { background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-family: monospace; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #FF5722; color: white; }";
echo ".resultado { margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 3px; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 Resetar Senha - IPIL News</h1>";

require_once __DIR__ . '/config.php';
$pdo = getDB();

// ✅ Seção 1: Listar utilizadores
echo "<div class='box'>";
echo "<h2>👥 Utilizadores Registados</h2>";

try {
    $stmt = $pdo->query("
        SELECT u.id, u.nome, u.email, u.ativo
        FROM users u
        ORDER BY u.id ASC
    ");
    
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Ativo</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['nome']) . "</td>";
            echo "<td><code>" . htmlspecialchars($user['email']) . "</code></td>";
            echo "<td>" . ($user['ativo'] ? '✓' : '✗') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhum utilizador encontrado.</p>";
    }
} catch (Exception $e) {
    echo "<p>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// ✅ Seção 2: Resetar senha
echo "<div class='box'>";
echo "<h2>🔑 Resetar Senha</h2>";
echo "<form method='POST'>";
echo "<label><strong>Email do Utilizador:</strong></label>";
echo "<input type='email' name='reset_email' placeholder='admin@ipil.ao' value='" . htmlspecialchars($_POST['reset_email'] ?? '') . "' required>";
echo "<br><label><strong>Nova Senha:</strong></label>";
echo "<input type='text' name='reset_senha' placeholder='Mínimo 8 caracteres' value='" . htmlspecialchars($_POST['reset_senha'] ?? '') . "' required>";
echo "<br><button type='submit' name='executar_reset' value='1'>🔐 Resetar Senha</button>";
echo "</form>";

if (isset($_POST['executar_reset'])) {
    $resetEmail = $_POST['reset_email'] ?? '';
    $resetSenha = $_POST['reset_senha'] ?? '';
    
    echo "<div class='resultado'>";
    echo "<h3>📊 Resultado</h3>";
    
    // Validações
    $erros = [];
    if (!filter_var($resetEmail, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email inválido";
    }
    if (strlen($resetSenha) < 8) {
        $erros[] = "Senha muito curta (mínimo 8 caracteres)";
    }
    
    if (count($erros) > 0) {
        echo "<div class='erro'>";
        echo "<strong>❌ Erros:</strong><br>";
        foreach ($erros as $erro) {
            echo "• " . htmlspecialchars($erro) . "<br>";
        }
        echo "</div>";
    } else {
        try {
            // Buscar utilizador
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$resetEmail]);
            $user = $stmt->fetch();
            
            if (!$user) {
                echo "<div class='erro'>";
                echo "<strong>❌ Utilizador não encontrado</strong>";
                echo "</div>";
            } else {
                // Gerar novo hash
                $novoHash = password_hash($resetSenha, PASSWORD_BCRYPT);
                
                // Actualizar BD
                $stmt = $pdo->prepare("UPDATE users SET senha_hash = ? WHERE id = ?");
                $stmt->execute([$novoHash, $user['id']]);
                
                echo "<div class='ok'>";
                echo "<strong>✅ Senha atualizada com sucesso!</strong><br>";
                echo "<p><strong>Email:</strong> <code>" . htmlspecialchars($resetEmail) . "</code></p>";
                echo "<p><strong>Nova Senha:</strong> <code>" . htmlspecialchars($resetSenha) . "</code></p>";
                echo "<p style='margin-top: 15px; color: #999;'>";
                echo "Agora pode fazer login com a nova senha.<br>";
                echo "Aceda a: <a href='../frontend/login.html' target='_blank'>Login</a>";
                echo "</p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='erro'>";
            echo "<strong>❌ Erro:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }
    
    echo "</div>";
}

echo "</div>";

// ✅ Seção 3: Aviso
echo "<div class='box aviso'>";
echo "<h2>⚠️ Informações Importantes</h2>";
echo "<ul>";
echo "<li><strong>Admin Padrão:</strong> admin@ipil.ao</li>";
echo "<li><strong>Senha Padrão do Admin:</strong> Admin@IPIL2024</li>";
echo "<li><strong>Esta ferramenta deveria estar protegida em produção!</strong></li>";
echo "<li>Se a senha estiver incorreta, use este formulário para resetar</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
