k<?php
require_once "../db.php";
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once "../user/auth_check.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (
      !isset($_POST['csrf_token']) ||
      !isset($_SESSION['csrf_token']) ||
      $_POST['csrf_token'] !== $_SESSION['csrf_token']
  ) {
      die("⚠️ CSRF 토큰 검증 실패");
  }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>글쓰기</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f4f4;
      padding: 30px;
    }
    .container {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 25px;
      color: #333;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input[type="text"],
    textarea,
    input[type="file"] {
      width: 100%;
      padding: 12px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }
    textarea {
      height: 200px;
      resize: vertical;
    }
    button {
      margin-top: 20px;
      background-color: #1e90ff;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
    }
    button:hover {
      background-color: #006ad1;
    }
    .bottom-links {
      margin-top: 20px;
      text-align: right;
    }
    .bottom-links a {
      margin-left: 10px;
      text-decoration: none;
      color: #1e90ff;
    }
    .bottom-links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📝 새 글 작성</h2>
    <form method="post" action="write_process.php" enctype="multipart/form-data">
      <label for="title">제목</label>
      <input type="text" name="title" id="title" required>

      <label for="content">내용</label>
      <textarea name="content" id="content" required></textarea>

      <label for="file">첨부 파일</label>
      <input type="file" name="upload" id="file">

      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <input type="hidden" name="board_id" value="2"> 

      <button type="submit">작성 완료</button>
    </form>

    <div class="bottom-links">
      <a href="list.php">목록으로</a>
    </div>
  </div>
</body>
</html>
