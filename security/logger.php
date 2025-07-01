<?php
function log_security_event(mysqli $conn, ?int $user_id, string $ip, string $action, string $detail = ''): void {
    $stmt = $conn->prepare("
        INSERT INTO security_logs (user_id, ip_address, action, detail, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isss", $user_id, $ip, $action, $detail);
    $stmt->execute();
    $stmt->close();
}
?>
