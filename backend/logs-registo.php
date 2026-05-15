<?php
/**
 * VISUALIZAR LOGS DE REGISTO
 * Acesse: http://localhost/TLP-Projetos-corrigido/ipil-news/backend/logs-registo.php
 */

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>";
echo "<html lang='pt'>";
echo "<head><meta charset='UTF-8'>";
echo "<title>Logs de Registo - IPIL News</title>";
echo "<style>";
echo "body { font-family: 'Courier New', monospace; margin: 20px; background: #1e1e1e; color: #ddd; }";
echo ".container { max-width: 900px; margin: 0 auto; }";
echo "h1 { color: #FF5722; }";
echo ".log-entry { background: #2d2d2d; padding: 10px; margin: 5px 0; border-left: 4px solid #FF5722; border-radius: 3px; font-size: 12px; }";
echo ".log-entry.success { border-left-color: #4CAF50; }";
echo ".log-entry.error { border-left-color: #f44336; }";
echo ".timestamp { color: #FFC107; font-weight: bold; }";
echo ".data { background: #1e1e1e; padding: 8px; margin: 5px 0; border-radius: 2px; }";
echo "button { background: #FF5722; color: white; border: none; padding: 10px 15px; cursor: pointer; margin: 10px 0; border-radius: 3px; }";
echo "button:hover { background: #E64A19; }";
echo "p { margin: 10px 0; }";
echo "</style></head><body>";
echo "<div class='container'>";
echo "<h1>📋 Logs de Registo (Cadastro)</h1>";

$logFile = __DIR__ . '/registo_debug.log';

echo "<p>";
if (file_exists($logFile)) {
    echo "📊 <strong>Ficheiro encontrado:</strong> " . basename($logFile) . "<br>";
    echo "📅 <strong>Última atualização:</strong> " . date('d/m/Y H:i:s', filemtime($logFile)) . "<br>";
    echo "📦 <strong>Tamanho:</strong> " . filesize($logFile) . " bytes<br>";
} else {
    echo "❌ <strong>Ficheiro de log não encontrado ainda.</strong> (Será criado após a primeira tentativa de registo)";
}
echo "</p>";

echo "<p>";
echo "<button onclick='location.reload();'>🔄 Recarregar</button>";
if (file_exists($logFile)) {
    echo "<button onclick='if(confirm(\"Tem certeza?\")) { fetch(\"?clear=1\", {method: \"POST\"}).then(() => location.reload()); }'>🗑️ Limpar Logs</button>";
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
        $data = json_decode($log, true);
        
        if (is_array($data)) {
            $cssClass = (isset($data['sucesso']) && $data['sucesso']) ? 'success' : 'error';
            echo "<div class='log-entry " . $cssClass . "'>";
            
            echo "<div class='timestamp'>⏰ " . ($data['timestamp'] ?? date('Y-m-d H:i:s')) . "</div>";
            echo "<div class='data'>";
            
            if (isset($data['sucesso']) && $data['sucesso']) {
                echo "✅ <strong>REGISTO BEM-SUCEDIDO</strong><br>";
                echo "📧 Email: <code>" . htmlspecialchars($data['email'] ?? '') . "</code><br>";
                echo "👤 Role: <strong>" . htmlspecialchars($data['role'] ?? '') . "</strong><br>";
                echo "🆔 User ID: " . ($data['user_id'] ?? '-') . "<br>";
            } else {
                echo "❌ <strong>ERRO NO REGISTO</strong><br>";
                echo "📧 Email: <code>" . htmlspecialchars($data['email'] ?? 'N/A') . "</code><br>";
                echo "👤 Role: " . htmlspecialchars($data['role'] ?? 'N/A') . "<br>";
                echo "🔑 Código: " . htmlspecialchars($data['codigo'] ?? 'N/A') . "<br>";
                echo "⚠️ Motivo: <strong>" . htmlspecialchars($data['erro'] ?? 'Desconhecido') . "</strong>";
            }
            
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='log-entry error'>";
            echo "<pre>" . htmlspecialchars($log) . "</pre>";
            echo "</div>";
        }
    }
    
    if ($logCount > 50) {
        echo "<p style='margin-top: 20px; color: #999;'>📝 Mostrando últimas 50 de " . $logCount . " tentativas.</p>";
    }
} else {
    echo "<div style='background: #2d2d2d; padding: 15px; border-radius: 3px; color: #999;'>";
    echo "📭 Nenhum log disponível ainda.<br>";
    echo "Tente registar-se na página para criar um log.";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>
