<?php
session_start();
require_once "../db.php";
require_once "../user/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("❌ 잘못된 접근입니다.");
}

$comment_id = (int)($_POST["comment_id"] ?? 0);
$post_id    = (int)($_POST["post_id"] ?? 0);

if ($comment_id <= 0 || $post_id <= 0) {
    die("❌ 잘못된 요청입니다.");
}

// 댓글 정보 불러오기
$stmt = $conn->prepare("SELECT * FROM board2_comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
$stmt->close();

if (!$comment) {
    die("❌ 댓글을 찾을 수 없습니다.");
}

$current_user_id = $_SESSION["user_id"];
$is_admin = $_SESSION["is_admin"] ?? 0;

// 권한 확인
if ($comment["user_id"] != $current_user_id && !$is_admin) {
    die("❌ 권한이 없습니다.");
}

// 댓글 삭제
$stmt = $conn->prepare("DELETE FROM board2_comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->close();

// 게시글 보기 페이지로 리다이렉트
header("Location: view.php?id=" . $post_id);
exit;
?>
