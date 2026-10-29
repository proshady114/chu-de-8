<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
startAppSession();
$notice = takeNotice();
$posts = $pdo->query('SELECT id, title, content, ai_summary FROM posts ORDER BY id DESC LIMIT 100')->fetchAll();
?>
<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Chủ đề 8 - CRUD API kết hợp AI</title><link rel="stylesheet" href="css/style.css"></head>
<body><main>
<h1>Chủ đề 8: Quản lý bài viết &amp; Tóm tắt AI</h1>
<p class="sub">PHP + WAMP64 + MySQL + API AI | Nhà cung cấp: <?= e(strtoupper(AI_PROVIDER)) ?></p>
<div class="toolbar"><a class="btn" href="create.php">+ Thêm bài viết</a>
<div><a class="btn gray" href="test_api.php">Kiểm tra API AI</a> <a class="btn gray" href="api/posts.php">Xem API JSON</a></div></div>
<?php if ($notice): ?><div class="alert <?= $notice['type'] === 'error' ? 'error' : 'success' ?>"><?= e($notice['message']) ?></div><?php endif; ?>
<section class="panel"><h2>Danh sách bài viết</h2><div class="scroll"><table>
<thead><tr><th>ID</th><th>Tiêu đề</th><th>Nội dung</th><th>Tóm tắt AI</th><th>Thao tác</th></tr></thead>
<tbody>
<?php foreach ($posts as $post): ?>
<tr><td><?= (int)$post['id'] ?></td>
<td><strong><?= e($post['title']) ?></strong></td>
<td><div class="preview"><?= e(mbSafeExcerpt($post['content'], 180)) ?></div></td>
<td class="summary"><?php if (trim((string)$post['ai_summary']) !== ''): ?><?= e($post['ai_summary']) ?><?php else: ?><em class="muted">Chưa có tóm tắt AI</em><?php endif; ?></td>
<td><div class="actions">
<a class="btn small" href="edit.php?id=<?= (int)$post['id'] ?>">Sửa</a>
<form class="inline" method="post" action="regenerate.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><button class="btn teal small" type="submit">Tạo lại AI</button></form>
<form class="inline" method="post" action="delete.php" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?')"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><button class="btn red small" type="submit">Xóa</button></form>
</div></td></tr>
<?php endforeach; ?>
<?php if (!$posts): ?><tr><td colspan="5">Chưa có bài viết nào. Hãy thêm một bài viết để thử AI.</td></tr><?php endif; ?>
</tbody></table></div></section>
<p class="help">Bản học tập chạy trên localhost. API REST demo: <code>api/posts.php</code>. Không đưa dự án lên Internet khi chưa bổ sung đăng nhập và phân quyền.</p>
</main></body></html>
<?php
// Fallback khi extension mbstring chua duoc bat.
function mbSafeExcerpt($text, $limit) {
    if (function_exists('mb_substr')) {
        return mb_substr((string)$text, 0, $limit, 'UTF-8');
    }
    return substr((string)$text, 0, $limit);
}
