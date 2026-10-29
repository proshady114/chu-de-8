<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ai_helper.php';
startAppSession();
$error = '';
$title = '';
$content = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    if (validPostInput($title, $content, $error)) {
        $aiError = '';
        $summary = generateAISummary($content, $aiError);
        $stmt = $pdo->prepare('INSERT INTO posts (title, content, ai_summary) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, $summary]);
        if ($summary === null) {
            setNotice('error', 'Đã lưu bài viết nhưng AI chưa tóm tắt được. ' . $aiError);
        } else {
            setNotice('success', 'Đã lưu bài viết và tạo tóm tắt AI thành công.');
        }
        redirectHome();
    }
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Thêm bài viết</title><link rel="stylesheet" href="css/style.css"></head>
<body><main><h1>Thêm bài viết mới</h1><p class="sub">Lưu bài viết vào MySQL và gọi AI tóm tắt tự động.</p>
<section class="panel">
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
<label for="title">Tiêu đề</label><input type="text" id="title" name="title" maxlength="255" value="<?= e($title) ?>" required>
<label for="content">Nội dung bài viết</label><textarea id="content" name="content" required><?= e($content) ?></textarea>
<div class="form-actions"><button class="btn" type="submit">Lưu &amp; Tạo tóm tắt AI</button><a class="btn gray" href="index.php">Quay lại</a></div>
</form></section></main></body></html>
