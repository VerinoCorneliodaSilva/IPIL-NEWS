<?php
require_once "config.php";

$pdo = getDB();

// pega a senha digitada (ou fixa para teste)
$senha_digitada = $_POST['senha'] ?? 'Admin@IPIL2024';
$email = "admin@ipil.ao";

// buscar senha na base de dados
$stmt = $pdo->prepare("SELECT senha_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    die("Admin não encontrado");
}

$senha_bd = $user['senha_hash'];

echo "Senha na BD: " . $senha_bd . "<br>";
echo "Senha digitada: " . $senha_digitada . "<br><br>";

// comparação direta (SEM hash)
if ($senha_digitada === $senha_bd) {
    echo "✅ Senha correta!";
} else {
    echo "❌ Senha incorreta!";
}