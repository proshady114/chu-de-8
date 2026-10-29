<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function startAppSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
function csrfToken() {
    startAppSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}
function verifyCsrf() {
    startAppSession();
    $posted = (string)($_POST['csrf_token'] ?? '');
    if ($posted === '' || !hash_equals(csrfToken(), $posted)) {
        http_response_code(403);
        exit('Phien lam viec khong hop le. Hay tai lai trang.');
    }
}
function setNotice($type, $message) {
    startAppSession();
    $_SESSION['notice'] = ['type' => $type, 'message' => $message];
}
function takeNotice() {
    startAppSession();
    $notice = $_SESSION['notice'] ?? null;
    unset($_SESSION['notice']);
    return $notice;
}
function redirectHome() {
    header('Location: index.php');
    exit;
}
function validPostInput($title, $content, &$error) {
    $title = trim((string)$title);
    $content = trim((string)$content);
    if ($title === '' || $content === '') {
        $error = 'Vui long nhap day du tieu de va noi dung.';
        return false;
    }
    if (strlen($title) > 255 || strlen($content) > 30000) {
        $error = 'Tieu de toi da 255 byte, noi dung toi da 30.000 byte.';
        return false;
    }
    return true;
}
