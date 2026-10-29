<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ai_helper.php';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) { http_response_code(403); exit('Chi cho phep kiem tra tren localhost.'); }
startAppSession();
$provider = strtolower((string)AI_PROVIDER);
$key = $provider === 'gemini' ? (getenv('GEMINI_API_KEY') ?: GEMINI_API_KEY) : (getenv('OPENAI_API_KEY') ?: OPENAI_API_KEY);
$model = $provider === 'gemini' ? GEMINI_MODEL : OPENAI_MODEL;
$ca = CA_BUNDLE_PATH;
$iniCA = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');
$result = null;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $result = generateAISummary('AI giup phan tich du lieu va ho tro con nguoi trong hoc tap, cong viec.', $error);
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Kiểm tra AI</title><link rel="stylesheet" href="css/style.css"></head>
<body><main><h1>Kiểm tra kết nối AI trên WAMP64</h1><p class="sub">Trang chỉ hiển thị trạng thái, KHÔNG hiển thị API key.</p>
<section class="panel">
<p><strong>Nhà cung cấp:</strong> <?= e(strtoupper($provider)) ?></p>
<p><strong>Model:</strong> <?= e($model) ?></p>
<p><strong>API key:</strong> <?= trim((string)$key) !== '' ? 'Đã cấu hình (không xác nhận key còn hợp lệ)' : 'CHƯA CẤU HÌNH' ?></p>
<p><strong>PHP cURL:</strong> <?= function_exists('curl_init') ? 'Đã bật' : 'CHƯA BẬT' ?></p>
<p><strong>PHP ini:</strong> <?= e(php_ini_loaded_file() ?: 'Không rõ') ?></p>
<p><strong>CA tùy chỉnh:</strong> <code><?= e($ca) ?></code> / <?= is_readable($ca) ? 'Đọc được' : 'Không thấy file; sẽ dùng cấu hình hệ thống' ?></p>
<p><strong>CA trong php.ini:</strong> <code><?= e($iniCA ?: '(chưa cấu hình)') ?></code></p>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="btn" type="submit">Gửi thử yêu cầu tóm tắt AI thật</button> <a class="btn gray" href="index.php">Về trang chủ</a></form>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<?php if ($result !== null): ?><div class="alert success"><strong>API gọi thành công!</strong><p><?= e($result) ?></p></div>
<?php else: ?><div class="alert error"><strong>API chưa thành công:</strong><p><?= e($error) ?></p></div><?php endif; ?>
<?php endif; ?>
</section><p class="help">Nếu cURL 60: tải <a href="https://curl.se/ca/cacert.pem">cacert.pem</a> vào C:/wamp64/certs/cacert.pem. Không tắt CURLOPT_SSL_VERIFYPEER.</p>
</main></body></html>
