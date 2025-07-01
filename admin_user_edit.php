<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["is_admin"] != 1 || !isset($_SESSION['2fa_verified'])) {
    header("Location: login.php");
    exit;
}
require_once "admin_check.php";
require_once "db.php";

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $target_user = trim($_POST["username"] ?? '');
    $new_password = trim($_POST["new_password"] ?? '');

    if ($target_user === '' || $new_password === '') {
        $message = "❗ 사용자명과 새 비밀번호를 모두 입력해주세요.";
    } else {
        // 사용자 존재 여부 확인
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $target_user);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $message = "❌ 해당 사용자가 존재하지 않습니다.";
        } else {
            $stmt->bind_result($user_id);
            $stmt->fetch();

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // 비밀번호 업데이트
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $user_id);
            if ($update->execute()) {
                $message = "✅ 비밀번호가 성공적으로 변경되었습니다.";
            } else {
                $message = "⚠️ 비밀번호 변경 실패. 다시 시도해주세요.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>사용자 비밀번호 변경</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f5f6fa; padding: 50px; }
    .container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { color: #2c3e50; margin-bottom: 20px; }
    input[type=text], input[type=password] {
      width: 100%; padding: 12px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc;
    }
    button {
      width: 100%; padding: 12px; background: #007bff; color: white; border: none;
      border-radius: 6px; font-size: 16px; cursor: pointer;
    }
    button:hover { background: #0056b3; }
    .msg { margin-top: 15px; color: #e74c3c; }
  </style>
</head>
<body>
  <div class="container">
    <h2>🔑 사용자 비밀번호 변경</h2>
    <form method="post">
      <input type="text" name="username" placeholder="사용자 아이디" required>
      <input type="password" name="new_password" placeholder="새 비밀번호" required>
      <button type="submit">비밀번호 변경</button>
    </form>
    <?php if ($message): ?>
      <p class="msg"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
  </div>
</body>
</html>
