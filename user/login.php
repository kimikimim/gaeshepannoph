<?php
session_start(); // 세션 시작
$error = $_SESSION['login_error'] ?? ''; // login_process.php에서 넘어온 에러 메시지
unset($_SESSION['login_error']); // 에러 메시지 사용 후 제거

// 이미 로그인된 경우 index.php로 리다이렉트 (선택 사항)
if (isset($_SESSION["user_id"])) {
    header("Location: /index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로그인</title>
    <script src="https://www.google.com/recaptcha/api.js?render=6LeN4nErAAAAANMOYFmZZCD-UnB6jLvfYnnNH7fe"></script>
    <script>
    grecaptcha.ready(function() {
        grecaptcha.execute('6LeN4nErAAAAANMOYFmZZCD-UnB6jLvfYnnNH7fe', {action: 'login'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
        });
    });
    </script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            margin-bottom: 25px;
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #007BFF;
            border: none;
            color: white;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 로그인</h2>
        <form method="post" action="login_process.php">
            <input type="text" name="username" placeholder="아이디" required>
            <input type="password" name="password" placeholder="비밀번호" required>
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            <input type="submit" value="로그인">
        </form>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    </div>
</body>
</html>