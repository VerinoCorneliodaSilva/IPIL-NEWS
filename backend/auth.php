<?php

require_once __DIR__ . "/config.php";

header("Content-Type: application/json");

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

match ($acao) {
    "login" => login(),
    "registar" => registar(),
    "logout" => logout(),
    default => jsonResponse(false, "Ação inválida")
};

function login() {

    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!$email || !$senha) {
        jsonResponse(false, "Campos vazios");
    }

    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT u.id, u.nome, u.senha_hash, u.ativo, r.nome AS role
        FROM users u
        JOIN roles r ON r.id = u.role_id
        WHERE u.email = ?
    ");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, "Utilizador não encontrado");
    }

    // 🔥 SENHA SIMPLES (SEM HASH)
    if ($senha !== $user['senha_hash']) {
        jsonResponse(false, "Senha incorreta");
    }

    if (!$user['ativo']) {
        jsonResponse(false, "Conta desativada");
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nome'] = $user['nome'];
    $_SESSION['role'] = $user['role'];

    jsonResponse(true, "Login OK");
}

function registar() {

    $nome = sanitizar($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $role = sanitizar($_POST['role'] ?? '');

    if (!$nome || !$email || !$senha || !$codigo || !$role) {
        jsonResponse(false, "Campos obrigatórios");
    }

    $pdo = getDB();

    // email duplicado
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        jsonResponse(false, "Email já existe");
    }

    // validar código
    $stmt = $pdo->prepare("
        SELECT vc.id, vc.role_id
        FROM validation_codes vc
        JOIN roles r ON r.id = vc.role_id
        WHERE vc.codigo = ? AND r.nome = ? AND vc.usado = 0
    ");

    $stmt->execute([$codigo, $role]);
    $vc = $stmt->fetch();

    if (!$vc) {
        jsonResponse(false, "Código inválido");
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO users (nome, email, senha_hash, role_id, validation_code_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$nome, $email, $senha, $vc['role_id'], $vc['id']]);

    $pdo->prepare("UPDATE validation_codes SET usado = 1 WHERE id = ?")
        ->execute([$vc['id']]);

    $pdo->commit();

    jsonResponse(true, "Conta criada");
}

function logout() {
    session_destroy();
    jsonResponse(true, "Logout feito");
}