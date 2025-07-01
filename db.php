<?php
$conn = new mysqli("localhost", "webuser", "jsca0606", "board_site");
if ($conn->connect_error) {
    die("DB 연결 실패: " . $conn->connect_error);
}
?>
