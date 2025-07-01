<?php
session_start();
require_once "../db.php";
require_once "../user/auth_check.php";

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int)($_GET["id"] ?? 0);

// 게시글 불러오기
$stmt = $conn->prepare("SELECT * FROM board2_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 권한 확인
if (!$post || ($_SESSION["user_id"] != $post["user_id"] && ($_SESSION["is_admin"] ?? 0) != 1)) {
    die("🚫 수정 권한 없음");
}

// 글 수정 처리
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // CSRF 확인
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die("⚠️ CSRF 토큰 검증 실패");
  }

  $title   = trim($_POST["title"]);
  $content = trim($_POST["content"]);
  $filename  = $post["filename"];
  $save_name = $post["save_name"];

  if (!empty($_FILES["upload"]["name"])) {
      $original_name = basename($_FILES["upload"]["name"]);
      $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
      $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'];

      // 확장자 체크
      if (!in_array($ext, $allowed_ext)) {
          die("❌ 업로드할 수 없는 파일 형식입니다.");
      }

      // MIME 체크
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $_FILES['upload']['tmp_name']);
      finfo_close($finfo);
      $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
      if (!in_array($mime, $allowed_mimes)) {
          die("❌ 잘못된 MIME 형식입니다.");
      }

      // 파일 저장
      $unique_name = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
      $upload_path = __DIR__ . "/uploads/" . $unique_name;

      if (!move_uploaded_file($_FILES["upload"]["tmp_name"], $upload_path)) {
          die("❌ 파일 업로드 실패");
      }

      // 기존 파일 삭제
      $old_file = __DIR__ . "/uploads/" . $post["save_name"];
      if (file_exists($old_file)) {
          unlink($old_file);
      }

      $filename  = $original_name;
      $save_name = $unique_name;
  }

  // DB 업데이트
  $stmt = $conn->prepare("UPDATE board2_posts SET title=?, content=?, filename=?, save_name=?, updated_at=NOW() WHERE id=?");
  $stmt->bind_param("ssssi", $title, $content, $filename, $save_name, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: view.php?id=$id");
  exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>글 수정</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f9f9f9; padding: 30px; }
    h2 { color: #2c3e50; margin-bottom: 20px; }
    form {
      background: white; padding: 25px; border-radius: 10px;
      max-width: 700px; margin: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    label { font-weight: bold; margin-top: 10px; display: block; }
    input[type="text"], textarea, input[type="file"] {
      width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;
      margin-top: 5px; font-size: 14px;
    }
    textarea { resize: vertical; height: 180px; }
    button {
      margin-top: 20px; background: #007bff; color: white;
      padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;
    }
    button:hover { background: #0056b3; }
    .current-file { margin-top: 10px; font-size: 14px; color: #555; }
    .cancel-link { margin-top: 20px; display: block; text-align: right; color: #007bff; text-decoration: none; }
    .cancel-link:hover { text-decoration: underline; }
  </style>
</head>
<body>

<h2>✏️ 글 수정</h2>

<form method="post" enctype="multipart/form-data">
  <label for="title">제목</label>
  <input type="text" name="title" id="title" value="<?= htmlspecialchars($post["title"]) ?>" required>

  <label for="content">내용</label>
  <textarea name="content" id="content" required><?= htmlspecialchars($post["content"]) ?></textarea>

  <?php if ($post["filename"]): ?>
    <div class="current-file">📎 현재 파일: <?= htmlspecialchars($post["filename"]) ?></div>
  <?php endif; ?>

  <label for="upload">파일 교체</label>
  <input type="file" name="upload" id="upload">

  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <button type="submit">수정 완료</button>
</form>

<a class="cancel-link" href="view.php?id=<?= $id ?>">← 돌아가기</a>

</body>
</html>
