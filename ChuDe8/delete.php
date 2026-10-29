<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Chi cho phep POST.'); }
verifyCsrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('ID khong hop le.'); }
$stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
$stmt->execute([$id]);
setNotice('success', 'Đã xóa bài viết.');
redirectHome();
