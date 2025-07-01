<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
require_once "../db.php";
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateCode($length = 6) {
    return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $code = trim($_POST["code"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "올바른 이메일 형식을 입력해주세요.";
    } else {
        if (!empty($code)) {
            $stmt = $conn->prepare("SELECT * FROM auth_codes WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result && $result["code"] === $code) {
                $_SESSION["verified_email"] = $email;
                header("Location: /index.php");
                exit;
            } else {
                $error = "인증번호가 올바르지 않습니다.";
            }
        } else {
            $stmt = $conn->prepare("SELECT * FROM banned_emails WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $banned = $stmt->get_result()->fetch_assoc();

            if ($banned) {
                $error = "해당 이메일은 인증이 차단되었습니다.";
            } else {
                $code = generateCode();
                $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                $stmt = $conn->prepare("INSERT INTO auth_codes (email, code, created_at, expires_at) VALUES (?, ?, NOW(), ?)");
                $stmt->bind_param("sss", $email, $code, $expires_at);
                $stmt->execute();

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = '1.of.kknock@gmail.com'; 
                    $mail->Password = 'gtogcmctfsgfwcpp'; 
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->CharSet = 'UTF-8';            
                    $mail->Encoding = 'base64';

                    $mail->setFrom('your@gmail.com', '인증센터');
                    $mail->addAddress($email);
                    $mail->Subject = '회원가입 인증번호';
                    $mail->Body    = "회원님의 인증번호는 $code 입니다. 10분안에 입력해주십시오.";

                    $mail->send();
                    $success = "인증번호가 이메일로 전송되었습니다.";
                } catch (Exception $e) {
                    $error = "메일 전송 실패: {$mail->ErrorInfo}";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>이메일 인증</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; height: 100vh; justify-content: center; align-items: center; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px; }
        input[type="email"], input[type="text"] {
            width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="submit"] {
            width: 100%; padding: 10px; background-color: #5c8df6; color: white; border: none; border-radius: 4px; cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #3c6de0;
        }
        .success { color: green; margin-bottom: 10px; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>📧 이메일 인증</h2>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="이메일 주소" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="text" name="code" placeholder="인증번호 (선택사항)">
            <input type="submit" value="인증 요청 / 확인">
        </form>
    </div>
</body>
</html>