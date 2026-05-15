<?php
/**
 * PAINEL DE DIAGNÓSTICO - IPIL News
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/painel-diagnostico.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Painel de Diagnóstico - IPIL News</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 0; background: #f5f5f5; }";
echo ".header { background: linear-gradient(135deg, #FF5722 0%, #FFC107 100%); color: white; padding: 30px; text-align: center; }";
echo ".header h1 { margin: 0; font-size: 28px; }";
echo ".container { max-width: 1200px; margin: 20px auto; }";
echo ".grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }";
echo ".card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }";
echo ".card-header { background: #FF5722; color: white; padding: 15px; font-weight: bold; font-size: 16px; }";
echo ".card-body { padding: 15px; }";
echo ".card a { display: block; background: #FF5722; color: white; padding: 12px; text-align: center; text-decoration: none; border-radius: 4px; margin: 10px 0; transition: background 0.3s; }";
echo ".card a:hover { background: #E64A19; }";
echo ".card p { margin: 10px 0; font-size: 14px; color: #666; line-height: 1.5; }";
echo ".status { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }";
echo ".status-ok { background: #4CAF50; color: white; }";
echo ".status-erro { background: #f44336; color: white; }";
echo ".status-info { background: #2196F3; color: white; }";
echo ".section-title { font-size: 20px; font-weight: bold; color: #FF5722; margin: 30px 0 15px 0; border-bottom: 2px solid #FF5722; padding-bottom: 10px; }";
echo ".info-box { background: #E3F2FD; border-left: 4px solid #2196F3; padding: 15px; margin: 15px 0; border-radius: 3px; }";
echo ".success-box { background: #E8F5E9; border-left: 4px solid #4CAF50; padding: 15px; margin: 15px 0; border-radius: 3px; }";
echo ".warning-box { background: #FFF3E0; border-left: 4px solid #FFC107; padding: 15px; margin: 15px 0; border-radius: 3px; }";
echo ".error-box { background: #FFEBEE; border-left: 4px solid #f44336; padding: 15px; margin: 15px 0; border-radius: 3px; }";
echo "code { background: #f5f5f5; padding: 3px 6px; border-radius: 3px; font-family: monospace; }";
echo ".back-link { margin-bottom: 20px; }";
echo ".back-link a { color: #FF5722; text-decoration: none; font-weight: bold; }";
echo ".back-link a:hover { text-decoration: underline; }";
echo "</style></head><body>";

echo "<div class='header'>";
echo "<h1>🔍 Painel de Diagnóstico - IPIL News Portal</h1>";
echo "<p>Ferramentas para diagnosticar e resolver problemas de autenticação</p>";
echo "</div>";

echo "<div class='container'>";

// ✅ Seção 1: Status do Sistema
echo "<div class='section-title'>📊 Status do Sistema</div>";

require_once __DIR__ . '/config.php';
try {
    $pdo = getDB();
    $users_count = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'];
    $codigos_count = $pdo->query("SELECT COUNT(*) as total FROM validation_codes WHERE usado = 0")->fetch()['total'];
    
    echo "<div class='grid'>";
    
    echo "<div class='card'>";
    echo "<div class='card-header'>✅ Base de Dados</div>";
    echo "<div class='card-body'>";
    echo "<p><span class='status status-ok'>Conectada</span></p>";
    echo "<p>Utilizadores: <strong>" . $users_count . "</strong></p>";
    echo "<p>Códigos disponíveis: <strong>" . $codigos_count . "</strong></p>";
    echo "</div>";
    echo "</div>";
    
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error-box'>";
    echo "<strong>❌ Erro de conexão à BD:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

// ✅ Seção 2: Problema: Senha Incorreta
echo "<div class='section-title'>🔐 Problema: Senha Incorreta no Login</div>";

echo "<div class='warning-box'>";
echo "<strong>⚠️ O que fazer se receber 'Senha incorreta':</strong>";
echo "</div>";

echo "<div class='grid'>";

// Card 1: Diagnosticar
echo "<div class='card'>";
echo "<div class='card-header'>1️⃣ Diagnosticar Problema</div>";
echo "<div class='card-body'>";
echo "<p>Veja exatamente o que está guardado na base de dados e teste a senha.</p>";
echo "<a href='diagnostico-senha.php' target='_blank'>🔍 Abrir Diagnóstico Completo</a>";
echo "</div>";
echo "</div>";

// Card 2: Resetar Senha
echo "<div class='card'>";
echo "<div class='card-header'>2️⃣ Resetar Senha</div>";
echo "<div class='card-body'>";
echo "<p>Se a senha estiver errada, resete-a para uma conhecida.</p>";
echo "<a href='resetar-senha.php' target='_blank'>🔑 Resetar Senha</a>";
echo "</div>";
echo "</div>";

// Card 3: Ver Logs
echo "<div class='card'>";
echo "<div class='card-header'>3️⃣ Ver Logs de Login</div>";
echo "<div class='card-body'>";
echo "<p>Veja todas as tentativas de login e erros.</p>";
echo "<a href='logs-login.php' target='_blank'>📋 Ver Logs</a>";
echo "</div>";
echo "</div>";

echo "</div>";

// ✅ Seção 3: Problema: Erro no Registo
echo "<div class='section-title'>📝 Problema: Erro ao Criar Conta</div>";

echo "<div class='grid'>";

// Card 1: Gerenciar Códigos
echo "<div class='card'>";
echo "<div class='card-header'>1️⃣ Códigos de Validação</div>";
echo "<div class='card-body'>";
echo "<p>Crie, veja e gerencie os códigos de validação para registo.</p>";
echo "<a href='gerenciar-codigos.php' target='_blank'>🔐 Gerenciar Códigos</a>";
echo "</div>";
echo "</div>";

// Card 2: Testar Registo
echo "<div class='card'>";
echo "<div class='card-header'>2️⃣ Testar Registo</div>";
echo "<div class='card-body'>";
echo "<p>Teste o formulário de registo antes de submeter.</p>";
echo "<a href='teste-registo.php' target='_blank'>🧪 Testar Registo</a>";
echo "</div>";
echo "</div>";

// Card 3: Ver Logs
echo "<div class='card'>";
echo "<div class='card-header'>3️⃣ Ver Logs de Registo</div>";
echo "<div class='card-body'>";
echo "<p>Veja todas as tentativas de registo e erros.</p>";
echo "<a href='logs-registo.php' target='_blank'>📋 Ver Logs</a>";
echo "</div>";
echo "</div>";

echo "</div>";

// ✅ Seção 4: Dados de Teste
echo "<div class='section-title'>🧪 Dados de Teste Disponíveis</div>";

echo "<div class='success-box'>";
echo "<strong>Admin Padrão (já criado):</strong><br>";
echo "Email: <code>admin@ipil.ao</code><br>";
echo "Senha: <code>Admin@IPIL2024</code>";
echo "</div>";

echo "<div class='info-box'>";
echo "<strong>Códigos de Validação (disponíveis):</strong><br>";
echo "Alunos: <code>20240001</code>, <code>20240002</code><br>";
echo "Professores: <code>F-2024-001</code><br>";
echo "Diretores: <code>D-2024-001</code>";
echo "</div>";

// ✅ Seção 5: Guia Rápido
echo "<div class='section-title'>⚡ Guia Rápido</div>";

echo "<div class='info-box'>";
echo "<h3>Se receber 'Senha Incorreta':</h3>";
echo "<ol>";
echo "<li>Aceda a <strong>Diagnosticar Problema</strong></li>";
echo "<li>Insira o email e a senha exata</li>";
echo "<li>Se disser '<strong>SENHA CORRECTA ✅</strong>' - o problema é noutro lado</li>";
echo "<li>Se disser '<strong>SENHA INCORRETA ❌</strong>' - use <strong>Resetar Senha</strong></li>";
echo "<li>Tente login novamente com a nova senha</li>";
echo "</ol>";
echo "</div>";

echo "<div class='info-box'>";
echo "<h3>Se receber 'Código Inválido' no Registo:</h3>";
echo "<ol>";
echo "<li>Aceda a <strong>Códigos de Validação</strong></li>";
echo "<li>Crie um novo código com <strong>'Adicionar Código'</strong></li>";
echo "<li>Use esse código no formulário de registo</li>";
echo "<li>Se erro persiste, verifique <strong>Ver Logs de Registo</strong></li>";
echo "</ol>";
echo "</div>";

// ✅ Seção 6: Outras Ferramentas
echo "<div class='section-title'>🛠️ Outras Ferramentas</div>";

echo "<div class='grid'>";

echo "<div class='card'>";
echo "<div class='card-header'>Debug Avançado</div>";
echo "<div class='card-body'>";
echo "<p>Teste autenticação com diferentes utilizadores.</p>";
echo "<a href='teste-auth.php' target='_blank'>🔍 Teste Auth</a>";
echo "</div>";
echo "</div>";

echo "<div class='card'>";
echo "<div class='card-header'>Debug de Senha</div>";
echo "<div class='card-body'>";
echo "<p>Verificar hash e password_verify em detalhe.</p>";
echo "<a href='debug-senha.php' target='_blank'>🔐 Debug Senha</a>";
echo "</div>";
echo "</div>";

echo "</div>";

// ✅ Seção 7: Voltar
echo "<div style='text-align: center; margin-top: 40px; padding: 20px; background: white; border-radius: 8px;'>";
echo "<p><a href='../frontend/login.html' style='color: #FF5722; text-decoration: none; font-weight: bold;'>👤 Ir para Login</a> | ";
echo "<a href='../frontend/registar.html' style='color: #FF5722; text-decoration: none; font-weight: bold;'>📝 Ir para Registo</a></p>";
echo "</div>";

echo "</div>";

echo "</body></html>";
?>
