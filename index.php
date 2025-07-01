<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>나의 게시판 사이트</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f9f9f9;
            padding: 40px;
            text-align: center;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        p {
            font-size: 18px;
            margin-top: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        li {
            margin: 10px 0;
        }
        a {
            text-decoration: none;
            color: #4a00e0;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>나의 게시판 사이트</h1>

    <?php if (isset($_SESSION["user_id"])): ?>
        <p>✅ 안녕하세요, <strong><?= htmlspecialchars($_SESSION["username"] ?? "사용자") ?></strong>님!</p>
        <ul>
            <li><a href="/board1/list.php">📁 게시판 1</a></li>
            <li><a href="/board2/list.php">📁 게시판 2</a></li>
            <li><a href="/user/logout.php">🔓 로그아웃</a></li>
        </ul>
    <?php else: ?>
        <p>로그인 또는 회원가입을 진행해주세요.</p>
        <ul>
            <li><a href="/user/login.php">🔑 로그인</a></li>
            <li><a href="/user/register.php">📝 회원가입</a></li>
            <li><a href="/user/verify_form.php">📧 이메일 인증</a></li>
        </ul>
    <?php endif; ?>
</body>
</html>