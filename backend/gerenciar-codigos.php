<?php
/**
 * GERENCIADOR DE CÓDIGOS DE VALIDAÇÃO
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/gerenciar-codigos.php
 * 
 * AVISO: Em produção, isto deveria estar protegido apenas para Admin!
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Gerenciar Códigos de Validação - IPIL News</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1000px; margin: 0 auto; }";
echo ".box { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }";
echo ".ok { border-left: 4px solid #4CAF50; }";
echo ".erro { border-left: 4px solid #f44336; }";
echo ".info { border-left: 4px solid #2196F3; }";
echo "h1 { color: #FF5722; }";
echo "h2 { color: #333; border-bottom: 2px solid #FF5722; padding-bottom: 10px; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #FF5722; color: white; }";
echo "tr:hover { background: #f9f9f9; }";
echo ".usado { color: #999; text-decoration: line-through; }";
echo ".disponivel { color: #4CAF50; font-weight: bold; }";
echo "input, select { padding: 8px; margin: 5px 0; }";
echo "button { background: #FF5722; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 3px; margin: 5px; }";
echo "button:hover { background: #E64A19; }";
echo ".form-group { margin: 15px 0; }";
echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo "code { background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-family: monospace; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>🔐 Gerenciador de Códigos de Validação - IPIL News</h1>";

require_once __DIR__ . '/config.php';
$pdo = getDB();

// ✅ Adicionar novo código
if (isset($_POST['adicionar_codigo'])) {
    $novo_codigo = trim($_POST['novo_codigo'] ?? '');
    $novo_role = $_POST['novo_role'] ?? '';

    if ($novo_codigo && $novo_role) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO validation_codes (codigo, role_id)
                SELECT ?, r.id FROM roles r WHERE r.nome = ?
            ");
            $stmt->execute([$novo_codigo, $novo_role]);

            echo "<div class='box ok'>";
            echo "<h3>✅ Código adicionado com sucesso!</h3>";
            echo "<p><code>" . htmlspecialchars($novo_codigo) . "</code> para <strong>" . htmlspecialchars($novo_role) . "</strong></p>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='box erro'>";
            echo "<h3>❌ Erro ao adicionar código:</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
}

// ✅ Seção 1: Adicionar novo código
echo "<div class='box info'>";
echo "<h2>➕ Adicionar Novo Código de Validação</h2>";
echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label for='novo_codigo'>Código:</label>";
echo "<input type='text' id='novo_codigo' name='novo_codigo' placeholder='Ex: 20240001 ou F-2024-001' required>";
echo "</div>";
echo "<div class='form-group'>";
echo "<label for='novo_role'>Tipo de Utilizador:</label>";
echo "<select id='novo_role' name='novo_role' required>";
echo "<option value=''>Seleccione...</option>";
echo "<option value='aluno'>Aluno</option>";
echo "<option value='professor'>Professor</option>";
echo "<option value='diretor'>Diretor</option>";
echo "</select>";
echo "</div>";
echo "<button type='submit' name='adicionar_codigo' value='1'>➕ Adicionar Código</button>";
echo "</form>";
echo "</div>";

// ✅ Seção 2: Códigos disponíveis (não usados)
echo "<div class='box'>";
echo "<h2>✅ Códigos Disponíveis (Não Usados)</h2>";

$stmt = $pdo->query("
    SELECT vc.id, vc.codigo, r.nome as role, vc.criado_em, COUNT(u.id) as utilizadores
    FROM validation_codes vc
    LEFT JOIN roles r ON r.id = vc.role_id
    LEFT JOIN users u ON u.validation_code_id = vc.id
    WHERE vc.usado = 0
    GROUP BY vc.id
    ORDER BY vc.criado_em DESC
");

$disponivos = $stmt->fetchAll();
echo "<p><strong>Total:</strong> " . count($disponivos) . " códigos disponíveis</p>";

if (count($disponivos) > 0) {
    echo "<table>";
    echo "<tr><th>Código</th><th>Tipo</th><th>Criado em</th><th>Ação</th></tr>";
    foreach ($disponivos as $cod) {
        echo "<tr>";
        echo "<td><code class='disponivel'>" . htmlspecialchars($cod['codigo']) . "</code></td>";
        echo "<td>" . ucfirst($cod['role']) . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($cod['criado_em'])) . "</td>";
        echo "<td>";
        echo "<form method='POST' style='display: inline;'>";
        echo "<input type='hidden' name='marcar_usado' value='" . $cod['id'] . "'>";
        echo "<button type='submit' onclick='return confirm(\"Tem certeza?\")'>🗑️ Marcar como Usado</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: #999;'>Nenhum código disponível. Adicione um novo acima.</p>";
}

echo "</div>";

// ✅ Marcar código como usado
if (isset($_POST['marcar_usado'])) {
    $codigo_id = (int)$_POST['marcar_usado'];
    try {
        $stmt = $pdo->prepare("UPDATE validation_codes SET usado = 1 WHERE id = ?");
        $stmt->execute([$codigo_id]);
        echo "<div class='box ok'>";
        echo "<h3>✅ Código marcado como usado</h3>";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div class='box erro'>";
        echo "<h3>❌ Erro:</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

// ✅ Seção 3: Códigos já usados
echo "<div class='box'>";
echo "<h2>🔒 Códigos Já Utilizados</h2>";

$stmt = $pdo->query("
    SELECT vc.id, vc.codigo, r.nome as role, u.nome as utilizador, u.email, vc.criado_em
    FROM validation_codes vc
    LEFT JOIN roles r ON r.id = vc.role_id
    LEFT JOIN users u ON u.validation_code_id = vc.id
    WHERE vc.usado = 1
    ORDER BY vc.criado_em DESC
    LIMIT 50
");

$usados = $stmt->fetchAll();
echo "<p><strong>Últimos 50 códigos usados:</strong></p>";

if (count($usados) > 0) {
    echo "<table>";
    echo "<tr><th>Código</th><th>Tipo</th><th>Utilizador</th><th>Email</th><th>Data</th></tr>";
    foreach ($usados as $cod) {
        echo "<tr>";
        echo "<td><code class='usado'>" . htmlspecialchars($cod['codigo']) . "</code></td>";
        echo "<td>" . ucfirst($cod['role']) . "</td>";
        echo "<td>" . ($cod['utilizador'] ? htmlspecialchars($cod['utilizador']) : '-') . "</td>";
        echo "<td>" . ($cod['email'] ? htmlspecialchars($cod['email']) : '-') . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($cod['criado_em'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: #999;'>Nenhum código foi ainda utilizado.</p>";
}

echo "</div>";

// ✅ Seção 4: Estatísticas
echo "<div class='box info'>";
echo "<h2>📊 Estatísticas</h2>";

$total = $pdo->query("SELECT COUNT(*) as total FROM validation_codes")->fetch()['total'];
$usados_count = $pdo->query("SELECT COUNT(*) as total FROM validation_codes WHERE usado = 1")->fetch()['total'];
$disponiveis_count = $total - $usados_count;

$roles_count = $pdo->query("
    SELECT r.nome, COUNT(vc.id) as total FROM validation_codes vc
    JOIN roles r ON r.id = vc.role_id
    GROUP BY r.id
")->fetchAll();

echo "<p><strong>Total de códigos:</strong> " . $total . "</p>";
echo "<p><strong>Disponíveis:</strong> " . $disponiveis_count . "</p>";
echo "<p><strong>Já utilizados:</strong> " . $usados_count . "</p>";
echo "<p><strong>Por tipo:</strong></p>";
echo "<ul>";
foreach ($roles_count as $role) {
    echo "<li>" . ucfirst($role['nome']) . ": " . $role['total'] . "</li>";
}
echo "</ul>";

echo "</div>";

echo "</div>";
echo "</body></html>";
?>
