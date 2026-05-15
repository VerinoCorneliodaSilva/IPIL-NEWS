<?php
/**
 * TESTE DE REGISTO
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/teste-registo.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Teste de Registo - IPIL News</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 700px; margin: 0 auto; }";
echo ".box { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo ".ok { border-left: 4px solid #4CAF50; }";
echo ".erro { border-left: 4px solid #f44336; }";
echo ".info { border-left: 4px solid #2196F3; }";
echo "h1 { color: #FF5722; }";
echo "h2 { color: #333; border-bottom: 2px solid #FF5722; padding-bottom: 10px; }";
echo "input, select { padding: 8px; width: 100%; margin: 8px 0; box-sizing: border-box; }";
echo "button { background: #FF5722; color: white; border: none; padding: 12px 20px; cursor: pointer; width: 100%; border-radius: 3px; font-size: 16px; }";
echo "button:hover { background: #E64A19; }";
echo "code { background: #f0f0f0; padding: 3px 6px; border-radius: 3px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #FF5722; color: white; }";
echo ".resultado { margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 3px; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 Teste de Registo - IPIL News</h1>";

require_once __DIR__ . '/config.php';
$pdo = getDB();

// ✅ Mostrar códigos disponíveis
echo "<div class='box info'>";
echo "<h2>📋 Códigos de Validação Disponíveis</h2>";

$stmt = $pdo->query("
    SELECT vc.codigo, r.nome as role
    FROM validation_codes vc
    JOIN roles r ON r.id = vc.role_id
    WHERE vc.usado = 0
    ORDER BY r.nome, vc.codigo
");

$codigos = $stmt->fetchAll();

if (count($codigos) > 0) {
    echo "<p><strong>Códigos disponíveis para usar no registo:</strong></p>";
    echo "<table>";
    echo "<tr><th>Código</th><th>Tipo de Utilizador</th></tr>";
    foreach ($codigos as $cod) {
        echo "<tr>";
        echo "<td><code>" . htmlspecialchars($cod['codigo']) . "</code></td>";
        echo "<td>" . ucfirst($cod['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='erro'>";
    echo "<strong>⚠️ Nenhum código disponível!</strong><br>";
    echo "Aceda a <code>gerenciar-codigos.php</code> para criar novos códigos.";
    echo "</div>";
}

echo "</div>";

// ✅ Formulário de teste
echo "<div class='box'>";
echo "<h2>🧪 Testar Registo</h2>";
echo "<form method='POST'>";
echo "<label>Nome Completo:</label>";
echo "<input type='text' name='test_nome' placeholder='Ex: João Silva' value='" . htmlspecialchars($_POST['test_nome'] ?? '') . "' required>";

echo "<label>E-mail:</label>";
echo "<input type='email' name='test_email' placeholder='exemplo@ipil.ao' value='" . htmlspecialchars($_POST['test_email'] ?? '') . "' required>";

echo "<label>Tipo de Utilizador:</label>";
echo "<select name='test_role' required>";
echo "<option value=''>Seleccione...</option>";
echo "<option value='aluno'>Aluno</option>";
echo "<option value='professor'>Professor</option>";
echo "<option value='diretor'>Diretor</option>";
echo "</select>";

echo "<label>Código de Validação:</label>";
echo "<input type='text' name='test_codigo' placeholder='Ex: 20240001' value='" . htmlspecialchars($_POST['test_codigo'] ?? '') . "' required>";

echo "<label>Senha (mín. 8 caracteres):</label>";
echo "<input type='password' name='test_senha' placeholder='Digite uma senha segura' required>";

echo "<br><button type='submit' name='test_registo' value='1'>✅ Testar Registo</button>";
echo "</form>";
echo "</div>";

// ✅ Processar teste
if (isset($_POST['test_registo'])) {
    $testNome = $_POST['test_nome'] ?? '';
    $testEmail = $_POST['test_email'] ?? '';
    $testRole = $_POST['test_role'] ?? '';
    $testCodigo = $_POST['test_codigo'] ?? '';
    $testSenha = $_POST['test_senha'] ?? '';

    echo "<div class='box resultado'>";
    echo "<h3>📊 Resultado do Teste</h3>";

    // Validações básicas
    $erros = [];
    if (!$testNome) $erros[] = "Nome não preenchido";
    if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) $erros[] = "Email inválido";
    if (!$testRole) $erros[] = "Role não seleccionado";
    if (!$testCodigo) $erros[] = "Código não preenchido";
    if (strlen($testSenha) < 8) $erros[] = "Senha muito curta (mín. 8)";

    if (count($erros) > 0) {
        echo "<div class='erro'>";
        echo "<strong>❌ Erros de validação:</strong><br>";
        foreach ($erros as $erro) {
            echo "• " . htmlspecialchars($erro) . "<br>";
        }
        echo "</div>";
    } else {
        // Testar registo
        try {
            // 1. Verificar se email já existe
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$testEmail]);
            if ($stmt->fetch()) {
                echo "<div class='erro'>";
                echo "<strong>❌ Email já registado</strong>";
                echo "</div>";
            } else {
                // 2. Verificar se código existe e é válido
                $stmt = $pdo->prepare("
                    SELECT vc.id, vc.role_id
                    FROM validation_codes vc
                    JOIN roles r ON r.id = vc.role_id
                    WHERE vc.codigo = ? AND r.nome = ? AND vc.usado = 0
                ");
                $stmt->execute([$testCodigo, $testRole]);
                $vc = $stmt->fetch();

                if (!$vc) {
                    echo "<div class='erro'>";
                    echo "<strong>❌ Código inválido</strong><br>";
                    echo "O código <code>" . htmlspecialchars($testCodigo) . "</code> não existe ou já foi utilizado.";
                    echo "</div>";
                } else {
                    // 3. Testar hash
                    $senhaHash = password_hash($testSenha, PASSWORD_BCRYPT);
                    echo "<div class='ok'>";
                    echo "<strong>✅ Registo seria bem-sucedido!</strong><br>";
                    echo "<p><strong>Dados:</strong></p>";
                    echo "<ul>";
                    echo "<li>Nome: " . htmlspecialchars($testNome) . "</li>";
                    echo "<li>Email: " . htmlspecialchars($testEmail) . "</li>";
                    echo "<li>Tipo: " . htmlspecialchars($testRole) . "</li>";
                    echo "<li>Código: " . htmlspecialchars($testCodigo) . " ✓</li>";
                    echo "<li>Senha: " . strlen($testSenha) . " caracteres ✓</li>";
                    echo "</ul>";
                    echo "</div>";

                    // 4. Mostrar o que seria guardado
                    echo "<p><strong>Hash que seria guardado:</strong></p>";
                    echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 3px; word-break: break-all; font-size: 11px;'>";
                    echo htmlspecialchars($senhaHash);
                    echo "</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='erro'>";
            echo "<strong>❌ Erro:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }

    echo "</div>";
}

// ✅ Links úteis
echo "<div class='box info'>";
echo "<h2>🔗 Links Úteis</h2>";
echo "<ul>";
echo "<li><a href='gerenciar-codigos.php' target='_blank'>🔐 Gerenciar Códigos de Validação</a></li>";
echo "<li><a href='logs-registo.php' target='_blank'>📋 Ver Logs de Registo</a></li>";
echo "<li><a href='logs-login.php' target='_blank'>📋 Ver Logs de Login</a></li>";
echo "<li><a href='debug-senha.php' target='_blank'>🔍 Debug de Senha</a></li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body></html>";
?>
