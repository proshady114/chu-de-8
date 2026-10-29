<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ai_helper.php';
startAppSession();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('ID khong hop le.'); }
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) { http_response_code(404); exit('Khong tim thay bai viet.'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $post['title'] = $title;
    $post['content'] = $content;
    if (validPostInput($title, $content, $error)) {
        if (isset($_POST['update_ai'])) {
            $aiError = '';
            $summary = generateAISummary($content, $aiError);
            if ($summary === null) {
                $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ? WHERE id = ?');
                $stmt->execute([$title, $content, $id]);
                setNotice('error', 'Đã sửa bài viết; giữ tóm tắt cũ vì AI lỗi: ' . $aiError);
            } else {
                $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ?, ai_summary = ? WHERE id = ?');
                $stmt->execute([$title, $content, $summary, $id]);
                setNotice('success', 'Cập nhật bài viết và tóm tắt AI thành công.');
            }
        } else {
            $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ? WHERE id = ?');
            $stmt->execute([$title, $content, $id]);
            setNotice('success', 'Đã cập nhật bài viết (giữ nguyên tóm tắt cũ).');
        }
        redirectHome();
    }
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sửa bài viết</title><link rel="stylesheet" href="css/style.css"></head>
<body><main><h1>Sửa bài viết #<?= (int)$id ?></h1><section class="panel">
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
<label for="title">Tiêu đề</label><input type="text" id="title" name="title" maxlength="255" value="<?= e($post['title']) ?>" required>
<label for="content">Nội dung</label><textarea name="content" id="content" required><?= e($post['content']) ?></textarea>
<p><label style="display:inline;font-weight:400"><input type="checkbox" name="update_ai" checked> Tự động tạo lại tóm tắt AI khi lưu</label></p>
<div class="form-actions"><button class="btn" type="submit">Cập nhật</button><a class="btn gray" href="index.php">Quay lại</a></div></form></section></main></body></html>
