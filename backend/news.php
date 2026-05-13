<?php

require_once __DIR__ . "/config.php";

// ✅ CORRIGIDO: Definir Content-Type JSON
header("Content-Type: application/json");

if (!autenticado()) {
    jsonResponse(false, "Login necessário");
}

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

match ($acao) {
    "listar"  => listar(),
    "detalhe" => detalhe(),
    "criar"   => criar(),
    "editar"  => editar(),
    "apagar"  => apagar(),
    default   => jsonResponse(false, "Inválido")
};

// ============================================================
// LISTAR — com paginação
// ✅ CORRIGIDO: Frontend espera { noticias, total_paginas, pagina_actual }
// ============================================================
function listar() {
    $pdo    = getDB();
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $limite = max(1, min(100, (int)($_GET['limite'] ?? 9)));
    $offset = ($pagina - 1) * $limite;

    $total = (int)$pdo->query("SELECT COUNT(*) FROM news WHERE publicado = 1")->fetchColumn();
    $totalPaginas = max(1, (int)ceil($total / $limite));

    $stmt = $pdo->prepare("
        SELECT n.id, n.titulo, n.corpo, n.imagem_url, n.criado_em, u.nome AS autor
        FROM news n
        JOIN users u ON u.id = n.autor_id
        WHERE n.publicado = 1
        ORDER BY n.criado_em DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limite, $offset]);
    $rows = $stmt->fetchAll();

    // Gerar resumo: primeiros 150 caracteres do corpo
    foreach ($rows as &$n) {
        $n['resumo'] = mb_substr(strip_tags($n['corpo']), 0, 150);
    }
    unset($n);

    jsonResponse(true, "OK", [
        "noticias"      => $rows,
        "total_paginas" => $totalPaginas,
        "pagina_actual" => $pagina
    ]);
}

// ============================================================
// DETALHE — buscar notícia por id
// ✅ CORRIGIDO: Ação em falta — chamada em index.html (abrirDetalhe)
// ============================================================
function detalhe() {
    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        jsonResponse(false, "ID inválido");
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT n.id, n.titulo, n.corpo, n.imagem_url, n.criado_em, u.nome AS autor
        FROM news n
        JOIN users u ON u.id = n.autor_id
        WHERE n.id = ? AND n.publicado = 1
    ");
    $stmt->execute([$id]);
    $noticia = $stmt->fetch();

    if (!$noticia) {
        jsonResponse(false, "Notícia não encontrada");
    }

    jsonResponse(true, "OK", ["noticia" => $noticia]);
}

// ============================================================
// CRIAR — apenas admin
// ============================================================
function criar() {
    requireRole("admin");

    $titulo    = sanitizar($_POST['titulo']    ?? '');
    $corpo     = trim($_POST['corpo']          ?? '');
    $imagemUrl = trim($_POST['imagem_url']     ?? '') ?: null;

    if (!$titulo || !$corpo) {
        jsonResponse(false, "Título e conteúdo são obrigatórios");
    }

    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO news (titulo, corpo, imagem_url, autor_id)
        VALUES (?, ?, ?, ?)
    ")->execute([$titulo, $corpo, $imagemUrl, $_SESSION['user_id']]);

    jsonResponse(true, "Notícia publicada com sucesso");
}

// ============================================================
// EDITAR — apenas admin
// ✅ CORRIGIDO: Ação em falta — chamada em admin.html
// ============================================================
function editar() {
    requireRole("admin");

    $id        = (int)($_POST['id']            ?? 0);
    $titulo    = sanitizar($_POST['titulo']    ?? '');
    $corpo     = trim($_POST['corpo']          ?? '');
    $imagemUrl = trim($_POST['imagem_url']     ?? '') ?: null;

    if (!$id || !$titulo || !$corpo) {
        jsonResponse(false, "Dados inválidos");
    }

    $pdo = getDB();
    $pdo->prepare("
        UPDATE news SET titulo = ?, corpo = ?, imagem_url = ?
        WHERE id = ?
    ")->execute([$titulo, $corpo, $imagemUrl, $id]);

    jsonResponse(true, "Notícia atualizada com sucesso");
}

// ============================================================
// APAGAR — apenas admin
// ✅ CORRIGIDO: Ação em falta — chamada em admin.html
// ============================================================
function apagar() {
    requireRole("admin");

    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        jsonResponse(false, "ID inválido");
    }

    $pdo = getDB();
    $pdo->prepare("DELETE FROM news WHERE id = ?")
        ->execute([$id]);

    jsonResponse(true, "Notícia apagada");
}
