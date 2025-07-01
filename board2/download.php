<?php
require_once "../db.php";

$id = (int)($_GET['id'] ?? 0);

// ✅ board2_posts 테이블로 변경
$stmt = $conn->prepare("SELECT filename, save_name FROM board2_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

// 파일 존재 여부 확인
if (!$post || !$post['save_name']) {
    echo "<h2>❌ 파일이 존재하지 않습니다.</h2>";
    exit;
}

// 디렉토리 경로 조작 방지
$upload_dir = realpath(__DIR__ . "/uploads") . DIRECTORY_SEPARATOR;
$file_path = $upload_dir . $post['save_name'];

if (!file_exists($file_path)) {
    echo "<h2>❌ 서버에 파일이 없습니다.</h2>";
    exit;
}

// 안전한 다운로드용 파일명
$safe_filename = str_replace(['"', "\r", "\n"], '', basename($post['filename']));

// 파일 다운로드 처리
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>
