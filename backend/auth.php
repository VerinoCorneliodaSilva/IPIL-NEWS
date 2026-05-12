<?php
/**
 * auth.php
 * Gestão de autenticação: login, registo, logout
 * Todas as queries usam prepared statements (PDO) – sem SQL Injection
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

// Determinar a acção solicitada
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

match ($acao) {
    'login'    => login(),
    'registar' => registar(),
    'logout'   => logout(),
    default    => jsonResponse(false, 'Acção inválida.')
};

// ============================================================
// FUNÇÃO: LOGIN
// ============================================================
function login(): void {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        jsonResponse(false, 'Preencha o e-mail e a senha.');
    }

    $pdo = getDB();

    // Buscar utilizador e o nome do seu role de uma só vez (JOIN)
    $stmt = $pdo->prepare(
        'SELECT u.id, u.nome, u.senha_hash, u.ativo, r.nome AS role
         FROM users u
         INNER JOIN roles r ON r.id = u.role_id
         WHERE u.email = :email
         LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // password_verify compara de forma segura (timing-safe)
    if (!$user || !password_verify($senha, $user['senha_hash'])) {
        jsonResponse(false, 'E-mail ou senha incorretos.');
    }

    if (!$user['ativo']) {
        jsonResponse(false, 'A sua conta está desactivada. Contacte o administrador.');
    }

    // Guardar dados essenciais na sessão (nunca a senha!)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nome']    = $user['nome'];
    $_SESSION['role']    = $user['role'];

    jsonResponse(true, 'Login efectuado com sucesso.', [
        'nome' => $user['nome'],
        'role' => $user['role'],
    ]);
}

// ============================================================
// FUNÇÃO: REGISTAR
// ============================================================
function registar(): void {
    $nome   = sanitizar($_POST['nome']   ?? '');
    $email  = trim($_POST['email']       ?? '');
    $senha  = $_POST['senha']            ?? '';
    $codigo = trim($_POST['codigo']      ?? '');   // Matrícula ou código de funcionário
    $role   = sanitizar($_POST['role']   ?? '');   // 'aluno', 'professor' ou 'diretor'

    // --- Validação básica dos campos ---
    if (empty($nome) || empty($email) || empty($senha) || empty($codigo) || empty($role)) {
        jsonResponse(false, 'Todos os campos são obrigatórios.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Endereço de e-mail inválido.');
    }

    if (strlen($senha) < 8) {
        jsonResponse(false, 'A senha deve ter pelo menos 8 caracteres.');
    }

    // Roles permitidos no registo público (admin NÃO pode ser criado aqui)
    $rolesPermitidos = ['aluno', 'professor', 'diretor'];
    if (!in_array($role, $rolesPermitidos, true)) {
        jsonResponse(false, 'Tipo de utilizador inválido.');
    }

    $pdo = getDB();

    // --- Verificar se o e-mail já está registado ---
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Este e-mail já está registado.');
    }

    // --- VALIDAÇÃO DO CÓDIGO IPIL ---
    // Aqui está a lógica central que garante que só pessoas do IPIL se registam:
    // 1. O código tem de existir na tabela validation_codes
    // 2. O role do código tem de corresponder ao role escolhido pelo utilizador
    // 3. O código ainda não pode ter sido usado por outro utilizador
    $stmt = $pdo->prepare(
        'SELECT vc.id
         FROM validation_codes vc
         INNER JOIN roles r ON r.id = vc.role_id
         WHERE vc.codigo = :codigo
           AND r.nome    = :role
           AND vc.usado  = 0
         LIMIT 1'
    );
    $stmt->execute([':codigo' => $codigo, ':role' => $role]);
    $vcRow = $stmt->fetch();

    if (!$vcRow) {
        jsonResponse(false, 'Código inválido ou já utilizado. Verifique o seu número de matrícula / código de funcionário.');
    }

    $validationCodeId = $vcRow['id'];

    // --- Obter o ID do role ---
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE nome = :role LIMIT 1');
    $stmt->execute([':role' => $role]);
    $roleRow = $stmt->fetch();
    $roleId  = $roleRow['id'];

    // --- Hash da senha (bcrypt, custo 12) ---
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    // --- Inserir utilizador e marcar o código como usado (transacção) ---
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO users (nome, email, senha_hash, role_id, validation_code_id)
             VALUES (:nome, :email, :senha_hash, :role_id, :vc_id)'
        );
        $stmt->execute([
            ':nome'      => $nome,
            ':email'     => $email,
            ':senha_hash'=> $senhaHash,
            ':role_id'   => $roleId,
            ':vc_id'     => $validationCodeId,
        ]);

        // Marcar o código como usado para que ninguém mais o utilize
        $stmt = $pdo->prepare('UPDATE validation_codes SET usado = 1 WHERE id = :id');
        $stmt->execute([':id' => $validationCodeId]);

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Erro ao registar utilizador: ' . $e->getMessage());
        jsonResponse(false, 'Erro ao criar conta. Tente novamente.');
    }

    jsonResponse(true, 'Conta criada com sucesso! Pode agora fazer login.');
}

// ============================================================
// FUNÇÃO: LOGOUT
// ============================================================
function logout(): void {
    $_SESSION = [];
    session_destroy();
    // Apagar o cookie de sessão do browser
    setcookie(session_name(), '', time() - 3600, '/', '', false, true);
    jsonResponse(true, 'Sessão terminada.');
}
