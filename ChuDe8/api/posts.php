<?php
// API JSON de minh hoa CRUD API + AI tren localhost (khong co auth -> KHONG public).
header('Content-Type: application/json; charset=utf-8');
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Chi cho phep localhost.'], JSON_UNESCAPED_UNICODE);
    exit;
}
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../ai_helper.php';
require_once __DIR__ . '/../helpers.php';
function outJson($code, $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (isset($_GET['id']) && !$id) {
    outJson(400, ['ok' => false, 'error' => 'ID khong hop le.']);
}
try {
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare('SELECT id, title, content, ai_summary FROM posts WHERE id = ?');
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            if (!$post) outJson(404, ['ok' => false, 'error' => 'Bai viet khong ton tai.']);
            outJson(200, ['ok' => true, 'post' => $post]);
        }
        $posts = $pdo->query('SELECT id, title, content, ai_summary FROM posts ORDER BY id DESC LIMIT 100')->fetchAll();
        outJson(200, ['ok' => true, 'posts' => $posts]);
    }

    if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        header('Allow: GET, POST, PUT, DELETE');
        outJson(405, ['ok' => false, 'error' => 'Phuong thuc khong ho tro.']);
    }
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) outJson(400, ['ok' => false, 'error' => 'Gui JSON hop le (Content-Type: application/json).']);

    if ($method === 'DELETE') {
        $deleteId = filter_var($body['id'] ?? $id, FILTER_VALIDATE_INT);
        if (!$deleteId) outJson(400, ['ok' => false, 'error' => 'Thieu id.']);
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$deleteId]);
        outJson(200, ['ok' => true, 'deleted' => $stmt->rowCount() > 0]);
    }

    $title = trim((string)($body['title'] ?? ''));
    $content = trim((string)($body['content'] ?? ''));
    $validationError = '';
    if (!validPostInput($title, $content, $validationError)) {
        outJson(422, ['ok' => false, 'error' => $validationError]);
    }
    $aiError = '';
    $summary = generateAISummary($content, $aiError);
    if ($method === 'POST') {
        $stmt = $pdo->prepare('INSERT INTO posts (title, content, ai_summary) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, $summary]);
        outJson(201, ['ok' => true, 'id' => (int)$pdo->lastInsertId(), 'ai_summary' => $summary, 'ai_error' => $summary === null ? $aiError : null]);
    }
    $editId = filter_var($body['id'] ?? $id, FILTER_VALIDATE_INT);
    if (!$editId) outJson(400, ['ok' => false, 'error' => 'Thieu id.']);
    $check = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
    $check->execute([$editId]);
    if (!$check->fetch()) outJson(404, ['ok' => false, 'error' => 'Bai viet khong ton tai.']);
    if ($summary === null) {
        $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ? WHERE id = ?');
        $stmt->execute([$title, $content, $editId]);
    } else {
        $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ?, ai_summary = ? WHERE id = ?');
        $stmt->execute([$title, $content, $summary, $editId]);
    }
    outJson(200, ['ok' => true, 'id' => $editId, 'ai_summary' => $summary, 'ai_error' => $summary === null ? $aiError : null]);
} catch (PDOException $e) {
    error_log('Loi API posts: ' . $e->getMessage());
    outJson(500, ['ok' => false, 'error' => 'Loi truy van MySQL. Kiem tra database.sql va db.php.']);
}
