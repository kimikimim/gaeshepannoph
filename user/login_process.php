<?php
session_start(); // 세션 시작
ini_set('session.cookie_path', '/'); // 세션 쿠키 경로 설정 (필요시)

require_once "../db.php"; // 데이터베이스 연결

$username = trim($_POST["username"] ?? '');
$password = trim($_POST["password"] ?? '');
$recaptcha = $_POST['g-recaptcha-response'] ?? '';

// 1. 빈 값 검사
if ($username === '' || $password === '' || $recaptcha === '') {
    $_SESSION['login_error'] = "❗ 모든 필드를 입력해주세요.";
    header("Location: login.php");
    exit;
}


$secret = "6LeN4nErAAAAAB-sc0iW5xaGkmVJUh0q5ZgC-JNU"; // 너의 v3 secret key
$response = $_POST['g-recaptcha-response'] ?? '';

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
$result = json_decode($verify, true);

if (
    !$result["success"] ||
    $result["action"] !== "login" ||
    $result["score"] < 0.5 
) {
    $_SESSION['login_error'] = "❌ reCAPTCHA 인증 실패 (봇으로 의심됨)";
    header("Location: login.php");
    exit;
}

// 사용자 정보 조회
$stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 로그인 성공
if ($user && password_verify($password, $user["password"])) {
    session_regenerate_id(true); // 세션 고정 공격 방지를 위해 ID 재생성
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $username;
    $_SESSION["is_admin"] = $user["is_admin"]; // is_admin 값 저장

    // 관리자면 관리자 페이지로, 아니면 일반 사용자 페이지로 리다이렉트
    if ($user["is_admin"] == 1) { // is_admin이 1인 경우 (데이터베이스 타입에 따라 조정)
        header("Location: /admin_login.php");
    } else {
        header("Location: /index.php");
    }
    exit;
} else {
    // 로그인 실패
    $_SESSION['login_error'] = "❌ 아이디 또는 비밀번호가 잘못되었습니다.";
    header("Location: login.php");
    exit;
}
?>