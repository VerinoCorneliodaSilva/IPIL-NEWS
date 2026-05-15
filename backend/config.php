<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'ipil_news');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

session_start();

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die(json_encode([
                "sucesso" => false,
                "mensagem" => "Erro de conexão com a base de dados: " . $e->getMessage(),
                "dados" => []
            ]));
        }
    }

    return $pdo;
}

function jsonResponse($success, $message, $data = []) {
    echo json_encode([
        "sucesso" => $success,
        "mensagem" => $message,
        "dados" => $data
    ]);
    exit;
}

function sanitizar($text) {
    return trim(strip_tags($text));
}

function autenticado(): bool {
    return isset($_SESSION['user_id']);
}

function requireRole($roles) {
    if (!autenticado()) {
        die("Não autenticado");
    }

    $roles = (array)$roles;

    if (!in_array($_SESSION['role'], $roles)) {
        die("Acesso negado");
    }
}