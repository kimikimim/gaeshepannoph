<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["is_admin"] != 1) {
    header("Location: /user/login.php");
    exit;
}
?>

<form action="verify_otp.php" method="post">
    OTP 입력: <input type="text" name="otp" required>
    <button type="submit">인증</button>
</form>