<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ai_helper.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Chi cho phep POST.'); }
verifyCsrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('ID khong hop le.'); }
$stmt = $pdo->prepare('SELECT content FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) { setNotice('error', 'Không tìm thấy bài viết.'); redirectHome(); }
$aiError = '';
$summary = generateAISummary($post['content'], $aiError);
if ($summary === null) {
    setNotice('error', 'Không tạo lại được tóm tắt. ' . $aiError);
} else {
    $stmt = $pdo->prepare('UPDATE posts SET ai_summary = ? WHERE id = ?');
    $stmt->execute([$summary, $id]);
    setNotice('success', 'Đã tạo lại tóm tắt AI cho bài viết #' . $id . '.');
}
redirectHome();
