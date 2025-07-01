<?php
session_start();

// IP 제한
$allowed_ip = '222.238.167.221';
if ($_SERVER['REMOTE_ADDR'] !== $allowed_ip) {
    die("❌ 접근이 제한된 관리자 IP입니다.");
}

require_once "../db.php";

$username = trim($_POST["username"] ?? '');
$password = trim($_POST["password"] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = "❗ 모든 필드를 입력해주세요.";
    header("Location: admin_login.php");
    exit;
}

// 관리자만 조회
$stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE username = ? AND is_admin = 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user["password"])) {
    session_regenerate_id(true);
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $username;
    $_SESSION["is_admin"] = 1;
    $_SESSION["user_agent"] = $_SERVER["HTTP_USER_AGENT"];

    header("Location: /admin_page.php");
    exit;
} else {
    $_SESSION['login_error'] = "❌ 아이디 또는 비밀번호가 잘못되었습니다.";
    header("Location: admin_login.php");
    exit;
}
?>

