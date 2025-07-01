<?php
session_start();
require_once "../db.php";
require_once "../user/auth_check.php";

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

$title    = trim($_POST['title']   ?? '');
$content  = trim($_POST['content'] ?? '');
$board_id = 2;

if ($title === '' || $content === '') {
    die("제목과 내용을 입력해주세요.");
}

$filename = '';
$saveName = '';

if (!empty($_FILES['upload']['name'])) {
    $upload_dir = __DIR__ . "/uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = $_FILES['upload']['name'];
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // ✅ 확장자 화이트리스트
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'];
    if (!in_array($ext, $allowed_exts)) {
        die("❌ 허용되지 않은 파일 형식입니다.");
    }

    // ✅ MIME 타입 확인
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['upload']['tmp_name']);
    finfo_close($finfo);
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
    if (!in_array($mime, $allowed_mimes)) {
        die("❌ 잘못된 MIME 타입입니다.");
    }

    // ✅ 안전한 저장 이름
    $saveName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $upload_dir . $saveName;

    if (!move_uploaded_file($_FILES["upload"]["tmp_name"], $target)) {
        die("파일 업로드 실패");
    }
}

$stmt = $conn->prepare("
    INSERT INTO board2_posts (board_id, user_id, title, content, filename, save_name, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("iissss", $board_id, $_SESSION['user_id'], $title, $content, $filename, $saveName);
$stmt->execute();
$stmt->close();

header("Location: list.php");
exit;