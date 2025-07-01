<?php
session_start();
require_once "vendor/autoload.php";
use OTPHP\TOTP;

if (!isset($_SESSION["user_id"]) || $_SESSION["is_admin"] != 1) {
    header("Location: /user/login.php");
    exit;
}

$otp = $_POST["otp"];
$secret = 'O4LDDO3O7ITBVM3Y';  

$totp = TOTP::create($secret);
if ($totp->verify($otp)) {
    $_SESSION["admin_verified"] = true;
    header("Location: /admin_page.php");
} else {
    $_SESSION["otp_error"] = "❌ OTP 인증 실패!";
    header("Location: /login.php");
}