<?php
/**
 * news.php
 * CRUD de notícias – apenas Admin pode criar, editar e apagar.
 * Leitura (feed) disponível para qualquer utilizador autenticado.
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!autenticado()) {
    jsonResponse(false, 'Acesso negado. Faça login primeiro.');
}

$acao   = $_POST['acao'] ?? $_GET['acao'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

match (true) {
    $acao === 'listar'                                    => listarNoticias(),
    $acao === 'detalhe'                                   => detalheNoticia(),
    $acao === 'criar'  && $method === 'POST'              => criarNoticia(),
    $acao === 'editar' && $method === 'POST'              => editarNoticia(),
    $acao === 'apagar' && $method === 'POST'              => apagarNoticia(),
    default                                               => jsonResponse(false, 'Acção inválida.')
};

// ============================================================
// LISTAR NOTÍCIAS (feed público para utilizadores autenticados)
// ============================================================
function listarNoticias(): void {
    $pdo    = getDB();
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limite = 10;
    $offset = ($pagina - 1) * $limite;

    // Total de notícias para paginação
    $total = $pdo->query('SELECT COUNT(*) FROM news WHERE publicado = 1')->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT n.id, n.titulo, n.imagem_url, n.criado_em,
                LEFT(n.corpo, 200) AS resumo,
                u.nome AS autor
         FROM news n
         INNER JOIN users u ON u.id = n.autor_id
         WHERE n.publicado = 1
         ORDER BY n.criado_em DESC
         LIMIT :limite OFFSET :offset'
    );
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    jsonResponse(true, 'OK', [
        'noticias'      => $stmt->fetchAll(),
        'total'         => (int)$total,
        'pagina_actual' => $pagina,
        'total_paginas' => (int)ceil($total / $limite),
    ]);
}

// ============================================================
// DETALHE DE UMA NOTÍCIA
// ============================================================
function detalheNoticia(): void {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID inválido.');
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT n.id, n.titulo, n.corpo, n.imagem_url, n.criado_em,
                u.nome AS autor
         FROM news n
         INNER JOIN users u ON u.id = n.autor_id
         WHERE n.id = :id AND n.publicado = 1
         LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $noticia = $stmt->fetch();

    if (!$noticia) {
        jsonResponse(false, 'Notícia não encontrada.');
    }

    jsonResponse(true, 'OK', ['noticia' => $noticia]);
}

// ============================================================
// CRIAR NOTÍCIA (só Admin)
// ============================================================
function criarNoticia(): void {
    requireRole('admin'); // Middleware de role

    $titulo    = sanitizar($_POST['titulo']    ?? '');
    $corpo     = trim($_POST['corpo']          ?? '');
    $imagemUrl = sanitizar($_POST['imagem_url'] ?? '');

    if (empty($titulo) || empty($corpo)) {
        jsonResponse(false, 'Título e corpo são obrigatórios.');
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO news (titulo, corpo, imagem_url, autor_id)
         VALUES (:titulo, :corpo, :imagem_url, :autor_id)'
    );
    $stmt->execute([
        ':titulo'     => $titulo,
        ':corpo'      => $corpo,
        ':imagem_url' => $imagemUrl ?: null,
        ':autor_id'   => $_SESSION['user_id'],
    ]);

    jsonResponse(true, 'Notícia criada com sucesso.', ['id' => (int)$pdo->lastInsertId()]);
}

// ============================================================
// EDITAR NOTÍCIA (só Admin)
// ============================================================
function editarNoticia(): void {
    requireRole('admin');

    $id        = (int)($_POST['id']            ?? 0);
    $titulo    = sanitizar($_POST['titulo']    ?? '');
    $corpo     = trim($_POST['corpo']          ?? '');
    $imagemUrl = sanitizar($_POST['imagem_url'] ?? '');

    if ($id <= 0 || empty($titulo) || empty($corpo)) {
        jsonResponse(false, 'Dados inválidos.');
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'UPDATE news
         SET titulo = :titulo, corpo = :corpo, imagem_url = :imagem_url
         WHERE id = :id'
    );
    $stmt->execute([
        ':titulo'     => $titulo,
        ':corpo'      => $corpo,
        ':imagem_url' => $imagemUrl ?: null,
        ':id'         => $id,
    ]);

    jsonResponse(true, 'Notícia actualizada com sucesso.');
}

// ============================================================
// APAGAR NOTÍCIA (só Admin) — soft delete via publicado=0
// ============================================================
function apagarNoticia(): void {
    requireRole('admin');

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, 'ID inválido.');
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('UPDATE news SET publicado = 0 WHERE id = :id');
    $stmt->execute([':id' => $id]);

    jsonResponse(true, 'Notícia removida com sucesso.');
}
