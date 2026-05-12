<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ipil_news;charset=utf8mb4",
        "root",
        ""
    );

    echo "✅ Conectado com sucesso à base de dados!";
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage();
}
?>