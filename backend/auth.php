<?php

require_once __DIR__ . "/config.php";

header("Content-Type: application/json");

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

if (!empty($acao)) {
    match ($acao) {
        "login" => login(),
        "registar" => registar(),
        "logout" => logout(),
        default => jsonResponse(false, "Ação inválida")
    };
} else {
    jsonResponse(false, "Ação não especificada");
}

function login() {
    try {
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

        // 🔍 DEBUG: Log da tentativa de login
        $debug_log = [
            "email" => $email,
            "password_length" => strlen($senha),
            "hash_exists" => !empty($user['senha_hash']),
            "account_active" => $user['ativo']
        ];

        // ✅ CORRIGIDO: Verificar senha com password_verify (compatível com password_hash do schema)
        $passwordMatch = password_verify($senha, $user['senha_hash']);
        $debug_log["password_verify_result"] = $passwordMatch;

        // Registar log em ficheiro para diagnóstico
        file_put_contents(
            __DIR__ . '/login_debug.log',
            date('Y-m-d H:i:s') . " - " . json_encode($debug_log) . "\n",
            FILE_APPEND
        );

        if (!$passwordMatch) {
            jsonResponse(false, "Senha incorreta");
        }

        if (!$user['ativo']) {
            jsonResponse(false, "Conta desativada");
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome']    = $user['nome'];
        $_SESSION['role']    = $user['role'];

        // ✅ CORRIGIDO: Retornar nome e role nos dados para o frontend guardar na sessionStorage
        jsonResponse(true, "Login OK", [
            "nome" => $user['nome'],
            "role" => $user['role']
        ]);
    } catch (Exception $e) {
        jsonResponse(false, "Erro no servidor: " . $e->getMessage());
    }
}

function registar() {
    try {
        $nome = sanitizar($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $role = sanitizar($_POST['role'] ?? '');

        // Log de debug
        $debug_log = [
            "acao" => "registar",
            "email" => $email,
            "role" => $role,
            "codigo" => $codigo,
            "timestamp" => date('Y-m-d H:i:s')
        ];

        if (!$nome || !$email || !$senha || !$codigo || !$role) {
            $debug_log["erro"] = "Campos obrigatórios vazios";
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Campos obrigatórios");
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $debug_log["erro"] = "Email inválido";
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Email inválido");
        }

        // Validar role
        $roles_validos = ['aluno', 'professor', 'diretor'];
        if (!in_array($role, $roles_validos)) {
            $debug_log["erro"] = "Role inválido: " . $role;
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Tipo de utilizador inválido");
        }

        // Validar comprimento da senha
        if (strlen($senha) < 8) {
            $debug_log["erro"] = "Senha muito curta";
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Senha deve ter pelo menos 8 caracteres");
        }

        $pdo = getDB();

        // Verificar email duplicado
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $debug_log["erro"] = "Email já existe";
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Email já está registado");
        }

        // Validar código de validação
        $stmt = $pdo->prepare("
            SELECT vc.id, vc.role_id, r.nome as role_nome
            FROM validation_codes vc
            JOIN roles r ON r.id = vc.role_id
            WHERE vc.codigo = ? AND r.nome = ? AND vc.usado = 0
        ");

        $stmt->execute([$codigo, $role]);
        $vc = $stmt->fetch();

        if (!$vc) {
            $debug_log["erro"] = "Código de validação não encontrado ou já foi usado";
            $debug_log["codigo_procurado"] = $codigo;
            $debug_log["role_procurado"] = $role;
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Código de validação inválido ou já foi utilizado");
        }

        $pdo->beginTransaction();

        try {
            // ✅ Hash seguro da senha
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("
                INSERT INTO users (nome, email, senha_hash, role_id, validation_code_id)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([$nome, $email, $senhaHash, $vc['role_id'], $vc['id']]);

            // Marcar código como usado
            $stmt2 = $pdo->prepare("UPDATE validation_codes SET usado = 1 WHERE id = ?");
            $stmt2->execute([$vc['id']]);

            $pdo->commit();

            // Log de sucesso
            $debug_log["sucesso"] = true;
            $debug_log["user_id"] = $pdo->lastInsertId();
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);

            jsonResponse(true, "Conta criada com sucesso! Pode agora fazer login.", [
                "user_id" => $pdo->lastInsertId(),
                "nome" => $nome,
                "email" => $email
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            $debug_log["erro"] = "Erro na transação: " . $e->getMessage();
            file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
            jsonResponse(false, "Erro ao criar conta: " . $e->getMessage());
        }

    } catch (Exception $e) {
        $debug_log["erro"] = "Erro global: " . $e->getMessage();
        file_put_contents(__DIR__ . '/registo_debug.log', json_encode($debug_log) . "\n", FILE_APPEND);
        jsonResponse(false, "Erro no servidor: " . $e->getMessage());
    }
}

function logout() {
    session_destroy();
    jsonResponse(true, "Logout feito");
}