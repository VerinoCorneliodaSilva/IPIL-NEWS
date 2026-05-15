<?php
/**
 * VISUALIZAR LOGS DE LOGIN
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/logs-login.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Logs de Login - IPIL News</title>";
echo "<style>";
echo "body { font-family: 'Courier New', monospace; margin: 20px; background: #1e1e1e; color: #ddd; }";
echo ".container { max-width: 900px; margin: 0 auto; }";
echo "h1 { color: #FF5722; }";
echo ".log-entry { background: #2d2d2d; padding: 10px; margin: 5px 0; border-left: 4px solid #FF5722; border-radius: 3px; font-size: 12px; }";
echo ".log-entry.success { border-left-color: #4CAF50; }";
echo ".log-entry.error { border-left-color: #f44336; }";
echo ".timestamp { color: #FFC107; }";
echo ".data { background: #1e1e1e; padding: 5px; margin: 5px 0; border-radius: 2px; }";
echo "button { background: #FF5722; color: white; border: none; padding: 10px 15px; cursor: pointer; margin: 10px 0; border-radius: 3px; }";
echo "button:hover { background: #E64A19; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>📋 Logs de Tentativas de Login</h1>";

$logFile = __DIR__ . '/login_debug.log';

echo "<p>";
if (file_exists($logFile)) {
    echo "📊 <strong>Ficheiro encontrado:</strong> " . basename($logFile) . "<br>";
    echo "📅 <strong>Última atualização:</strong> " . date('d/m/Y H:i:s', filemtime($logFile)) . "<br>";
    echo "📦 <strong>Tamanho:</strong> " . filesize($logFile) . " bytes<br>";
} else {
    echo "❌ <strong>Ficheiro de log não encontrado ainda.</strong> (Será criado após a primeira tentativa de login)";
}
echo "</p>";

echo "<p>";
echo "<button onclick='location.reload();'>🔄 Recarregar</button>";
if (file_exists($logFile)) {
    echo "<button onclick='fetch(\"?clear=1\", {method: \"POST\"}).then(() => location.reload());'>🗑️ Limpar Logs</button>";
}
echo "</p>";

// Limpar logs
if (isset($_GET['clear'])) {
    if (file_exists($logFile)) {
        unlink($logFile);
        echo "<div style='background: #4CAF50; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
        echo "✅ Logs limpos com sucesso!";
        echo "</div>";
    }
}

// Mostrar logs
if (file_exists($logFile)) {
    $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logCount = count($logs);
    
    echo "<p><strong>Total de tentativas:</strong> " . $logCount . "</p>";
    echo "<hr style='border: 0; border-top: 1px solid #3d3d3d;'>";
    
    // Mostrar últimas 50 linhas
    $recent = array_slice($logs, max(0, $logCount - 50), 50);
    
    foreach (array_reverse($recent) as $log) {
        echo "<div class='log-entry'>";
        
        // Parse do log
        if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - (.+)$/', $log, $matches)) {
            $timestamp = $matches[1];
            $data = json_decode($matches[2], true);
            
            echo "<div class='timestamp'>⏰ " . $timestamp . "</div>";
            
            if (is_array($data)) {
                echo "<div class='data'>";
                echo "📧 Email: <strong>" . htmlspecialchars($data['email'] ?? 'N/A') . "</strong><br>";
                echo "🔐 Senha: " . ($data['password_length'] ?? '0') . " caracteres<br>";
                echo "✓ password_verify(): <strong>";
                
                if ($data['password_verify_result'] === true) {
                    echo "<span style='color: #4CAF50;'>TRUE (Correcta)</span>";
                } else if ($data['password_verify_result'] === false) {
                    echo "<span style='color: #f44336;'>FALSE (Incorreta)</span>";
                } else {
                    echo "UNKNOWN";
                }
                echo "</strong><br>";
                
                echo "👤 Conta Ativa: " . ($data['account_active'] ? 'Sim ✓' : 'Não ✗') . "<br>";
                echo "</div>";
            }
        } else {
            echo "<pre>" . htmlspecialchars($log) . "</pre>";
        }
        
        echo "</div>";
    }
    
    if ($logCount > 50) {
        echo "<p style='margin-top: 20px; color: #999;'>📝 Mostrando últimas 50 de " . $logCount . " tentativas. As logs mais antigas não são mostradas.</p>";
    }
} else {
    echo "<div style='background: #2d2d2d; padding: 15px; border-radius: 3px; color: #999;'>";
    echo "📭 Nenhum log disponível ainda.<br>";
    echo "Tente fazer login na página para criar um log.";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>
