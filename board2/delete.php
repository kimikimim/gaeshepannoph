<?php
session_start();
require_once "../db.php";
require_once "../user/auth_check.php";  // 로그인 확인

// CSRF 토큰 검증
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("⚠️ CSRF 토큰 검증 실패");
    }
}

$id = (int)($_POST["id"] ?? 0);

// 게시글 조회
$stmt = $conn->prepare("SELECT * FROM board2_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

$current_user_id = $_SESSION["user_id"] ?? 0;
$is_admin = $_SESSION["is_admin"] ?? 0;
if ($post["user_id"] != $current_user_id && !$is_admin) {
    die("❌ 삭제 권한이 없습니다.");
}

// 첨부파일 삭제 (있을 경우)
if (!empty($post['save_name'])) {
    $file_path = realpath(__DIR__ . "/uploads") . DIRECTORY_SEPARATOR . $post['save_name'];
    if (file_exists($file_path)) {
        unlink($file_path);  // 실제 파일 제거
    }
}

// 댓글 삭제
/*
$stmt = $conn->prepare("DELETE FROM board2_comments WHERE post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
*/

// 게시글 삭제
$stmt = $conn->prepare("DELETE FROM board2_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: list.php");
exit;
?>