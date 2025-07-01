<?php
function logSecurityAction($message) {
    $logFile = '/var/log/admin_action.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $logFile);
}
require_once "db.php";

// 관리자 접속 로그 기록
//logSecurityAction($_SESSION['user_id'] ?? null, $_SERVER['REMOTE_ADDR'], 'ADMIN_ACCESS', '관리자 페이지 접속');

$post1_count = $conn->query("SELECT COUNT(*) AS count FROM board1_posts")->fetch_assoc()["count"] ?? 0;
$post2_count = $conn->query("SELECT COUNT(*) AS count FROM board2_posts")->fetch_assoc()["count"] ?? 0;
$user_count = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()["count"] ?? 0;
$banned_count = $conn->query("SELECT COUNT(*) AS count FROM users WHERE is_banned = 1")->fetch_assoc()["count"] ?? 0;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>🔐 관리자 페이지</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f6fa;
      padding: 50px;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 {
      color: #2c3e50;
      margin-bottom: 20px;
    }
    .summary {
      background: #f1f3f5;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 30px;
    }
    .summary p {
      margin: 6px 0;
    }
    .menu {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
    }
    .menu a {
      flex: 1;
      text-align: center;
      padding: 12px;
      background: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
    }
    .menu a:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔐 관리자 대시보드</h1>
    <p>접속 IP: <?= htmlspecialchars($_SERVER['REMOTE_ADDR']) ?></p>

    <div class="summary">
      <p>📄 Board1 게시글 수: <strong><?= $post1_count ?></strong></p>
      <p>📄 Board2 게시글 수: <strong><?= $post2_count ?></strong></p>
      <p>👥 전체 사용자 수: <strong><?= $user_count ?></strong></p>
      <p>⛔ 차단된 사용자 수: <strong><?= $banned_count ?></strong></p>
    </div>

    <div class="menu">
      <a href="/board1/list.php">📁 Board1 관리</a>
      <a href="/board2/list.php">📁 Board2 관리</a>
      <a href="/user/list.php">👤 사용자 목록</a>
      <a href="/user/logout.php">🚪 로그아웃</a>
    </div>
  </div>
</body>
</html>