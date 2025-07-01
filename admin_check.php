<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    die("접근이 제한되었습니다.");
}

$allowed_ips = [
    '127.0.0.1',
    '123.45.67.89'  
];

$client_ip = $_SERVER['REMOTE_ADDR'];

if (!in_array($client_ip, $allowed_ips)) {
    die("🚫 허용되지 않은 IP 주소입니다.");
}
?>
