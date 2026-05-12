<?php
/**
 * validation.php
 * Gestão de códigos de validação – apenas Admin
 * O Admin cria os códigos antes de distribuir às pessoas do IPIL.
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

requireRole('admin');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

match ($acao) {
    'criar'  => criarCodigo(),
    'listar' => listarCodigos(),
    'apagar' => apagarCodigo(),
    default  => jsonResponse(false, 'Acção inválida.')
};

// ============================================================
// CRIAR CÓDIGO DE VALIDAÇÃO
// O Admin insere o número de matrícula do aluno ou o código do
// funcionário e define o role correspondente.
// ============================================================
function criarCodigo(): void {
    $codigo = trim($_POST['codigo'] ?? '');
    $role   = sanitizar($_POST['role'] ?? '');

    if (empty($codigo) || empty($role)) {
        jsonResponse(false, 'Código e role são obrigatórios.');
    }

    $rolesPermitidos = ['aluno', 'professor', 'diretor'];
    if (!in_array($role, $rolesPermitidos, true)) {
        jsonResponse(false, 'Role inválido. Use: aluno, professor ou diretor.');
    }

    $pdo = getDB();

    // Verificar se o código já existe (cada matrícula é única no IPIL)
    $stmt = $pdo->prepare('SELECT id FROM validation_codes WHERE codigo = :codigo LIMIT 1');
    $stmt->execute([':codigo' => $codigo]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Este código já existe no sistema.');
    }

    // Obter o ID do role
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE nome = :role LIMIT 1');
    $stmt->execute([':role' => $role]);
    $roleRow = $stmt->fetch();
    if (!$roleRow) {
        jsonResponse(false, 'Role não encontrado na base de dados.');
    }

    // Inserir o código
    $stmt = $pdo->prepare(
        'INSERT INTO validation_codes (codigo, role_id) VALUES (:codigo, :role_id)'
    );
    $stmt->execute([':codigo' => $codigo, ':role_id' => $roleRow['id']]);

    jsonResponse(true, "Código '{$codigo}' criado para o role '{$role}'.");
}

// ============================================================
// LISTAR TODOS OS CÓDIGOS (com estado: usado / disponível)
// ============================================================
function listarCodigos(): void {
    $pdo  = getDB();
    $stmt = $pdo->query(
        'SELECT vc.id, vc.codigo, r.nome AS role, vc.usado, vc.criado_em,
                u.nome AS utilizado_por
         FROM validation_codes vc
         INNER JOIN roles r ON r.id = vc.role_id
         LEFT JOIN users u ON u.validation_code_id = vc.id
         ORDER BY vc.criado_em DESC'
    );

    jsonResponse(true, 'OK', ['codigos' => $stmt->fetchAll()]);
}

// ============================================================
// APAGAR CÓDIGO (só se ainda não foi usado)
// ============================================================
function apagarCodigo(): void {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID inválido.');
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT usado FROM validation_codes WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        jsonResponse(false, 'Código não encontrado.');
    }

    if ($row['usado']) {
        jsonResponse(false, 'Não é possível apagar um código já utilizado.');
    }

    $stmt = $pdo->prepare('DELETE FROM validation_codes WHERE id = :id');
    $stmt->execute([':id' => $id]);

    jsonResponse(true, 'Código apagado com sucesso.');
}
