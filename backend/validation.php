<?php

require_once __DIR__ . "/config.php";

requireRole("admin");

header("Content-Type: application/json");

$acao = $_POST['acao'] ?? '';

match ($acao) {
    "criar" => criar(),
    "listar" => listar(),
    "apagar" => apagar(),
    default => jsonResponse(false, "Inválido")
};

function criar() {

    $codigo = trim($_POST['codigo'] ?? '');
    $role = sanitizar($_POST['role'] ?? '');

    if (!$codigo || !$role) {
        jsonResponse(false, "Dados inválidos");
    }

    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT id FROM validation_codes WHERE codigo = ?");
    $stmt->execute([$codigo]);

    if ($stmt->fetch()) {
        jsonResponse(false, "Já existe");
    }

    $stmt = $pdo->prepare("SELECT id FROM roles WHERE nome = ?");
    $stmt->execute([$role]);
    $roleId = $stmt->fetch();

    if (!$roleId) {
        jsonResponse(false, "Role inválido");
    }

    $pdo->prepare("
        INSERT INTO validation_codes (codigo, role_id)
        VALUES (?, ?)
    ")->execute([$codigo, $roleId['id']]);

    jsonResponse(true, "Criado");
}

function listar() {

    $pdo = getDB();

    // CORRIGIDO: Incluir nome do utilizador que usou o codigo (utilizado_por)
    $stmt = $pdo->query("
        SELECT vc.*, r.nome AS role, u.nome AS utilizado_por
        FROM validation_codes vc
        JOIN roles r ON r.id = vc.role_id
        LEFT JOIN users u ON u.validation_code_id = vc.id
    ");

    // CORRIGIDO: Frontend espera json.dados.codigos (array dentro de chave codigos)
    jsonResponse(true, "OK", ["codigos" => $stmt->fetchAll()]);
}

function apagar() {

    $id = (int)($_POST['id'] ?? 0);

    $pdo = getDB();

    $pdo->prepare("DELETE FROM validation_codes WHERE id = ?")
        ->execute([$id]);

    jsonResponse(true, "Apagado");
}