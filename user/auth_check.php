<?php
session_start();
require_once "../db.php";

// 로그인 안 된 사용자 차단
if (!isset($_SESSION["user_id"])) {
    die("🚫 로그인한 사용자만 접근할 수 있습니다.");
}

// 세션 정보에 관리자 여부가 없으면 DB에서 불러와 저장
if (!isset($_SESSION["is_admin"])) {
    $user_id = $_SESSION["user_id"];
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($is_admin);
    if ($stmt->fetch()) {
        $_SESSION["is_admin"] = (int)$is_admin;
    } else {
        $_SESSION["is_admin"] = 0;
    }
    $stmt->close();
}
?>