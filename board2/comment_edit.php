<?php
session_start();
require_once "../db.php";
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$id = (int)($_GET["id"] ?? 0);
$post_id = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("⚠️ CSRF 토큰 검증 실패");
    }
}

$stmt = $conn->prepare("SELECT * FROM board2_comments WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$com = $stmt->get_result()->fetch_assoc();

$current_user_id = $_SESSION["user_id"] ?? 0;
$is_admin = $_SESSION["is_admin"] ?? 0;

if (!$com || ($com["user_id"] != $current_user_id && !$is_admin)) {
    die("❌ 권한 없음");
}

$post_id = $com["post_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new = trim($_POST["content"]);
    $u = $conn->prepare("UPDATE board2_comments SET content=?, updated_at=NOW() WHERE id=?");
    $u->bind_param("si", $new, $id);
    $u->execute();
    header("Location: view.php?id=$post_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>댓글 수정</title>
    <style>
        body {
            font-family: 'Pretendard', sans-serif;
            background-color: #f8f9fa;
            padding: 50px;
            display: flex;
            justify-content: center;
        }
        .comment-edit-box {
            background: white;
            border: 1px solid #ddd;
            padding: 30px;
            width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        h2 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
        }
        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: none;
            margin-bottom: 20px;
        }
        button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="comment-edit-box">
        <h2>✏️ 댓글 수정</h2>
        <form method="post">
            <textarea name="content" required><?= htmlspecialchars($com["content"]) ?></textarea>
            <br>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <button type="submit">저장</button>
        </form>
    </div>
</body>
</html>
