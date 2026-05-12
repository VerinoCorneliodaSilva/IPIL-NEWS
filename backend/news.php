<?php

require_once __DIR__ . "/config.php";

if (!autenticado()) {
    jsonResponse(false, "Login necessário");
}

$acao = $_GET['acao'] ?? '';

match ($acao) {
    "listar" => listar(),
    "criar" => criar(),
    default => jsonResponse(false, "Inválido")
};

function listar() {

    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT n.*, u.nome AS autor
        FROM news n
        JOIN users u ON u.id = n.autor_id
    ");

    jsonResponse(true, "OK", $stmt->fetchAll());
}

function criar() {

    requireRole("admin");

    $titulo = sanitizar($_POST['titulo'] ?? '');
    $corpo = trim($_POST['corpo'] ?? '');

    $pdo = getDB();

    $pdo->prepare("
        INSERT INTO news (titulo, corpo, autor_id)
        VALUES (?, ?, ?)
    ")->execute([$titulo, $corpo, $_SESSION['user_id']]);

    jsonResponse(true, "Criado");
}