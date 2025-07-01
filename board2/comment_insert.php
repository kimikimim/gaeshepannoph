<?php
session_start();
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none';");
require_once "../db.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("⚠️ CSRF 토큰 검증 실패");
    }
}

if (!isset($_SESSION["user_id"])) {
    die("🚫 로그인 필요");
}

$post_id = isset($_POST["post_id"]) ? (int)$_POST["post_id"] : 0;
$comment = isset($_POST["comment"]) ? trim($_POST["comment"]) : '';

if ($post_id <= 0 || $comment === '') {
    die("⚠️ 유효하지 않은 입력입니다.");
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("INSERT INTO board2_comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
if (!$stmt) {
    die("❌ prepare 실패: " . $conn->error);
}

if (!$stmt->bind_param("iis", $post_id, $user_id, $comment)) {
    die("❌ bind_param 실패: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("❌ execute 실패: " . $stmt->error);
}

$stmt->close();
$conn->close();

header("Location: view.php?id=" . $post_id);
exit;
