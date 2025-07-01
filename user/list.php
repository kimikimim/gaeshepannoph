<?php
session_start();

// -- 이 부분은 var_dump 출력으로 정상 확인되었습니다.
// if (!isset($_SESSION["user_id"])) {
//     header("Location: /user/login.php");
//     exit;
// }

require_once "../user/auth_check.php";
echo "<pre>";
var_dump("--- auth_check.php 포함 완료 ---");
echo "</pre>";

require_once "../db.php";
echo "<pre>";
var_dump("--- db.php 포함 완료 ---");
var_dump("db connection status: ", (bool)$conn); // $conn 객체가 생성되었는지 확인
echo "</pre>";

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}   

// POST 요청이 아니므로 이 블록은 실행되지 않습니다.
// if ($_SERVER["REQUEST_METHOD"] === "POST") { ... }

$board_id = 1;                           

$title   = trim($_GET['title']  ?? '');
$author  = trim($_GET['author'] ?? '');
$order   = ($_GET['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$sql    = "
  SELECT p.id, p.title, p.created_at, u.username
    FROM board1_posts  p
    JOIN users u ON p.user_id = u.id
   WHERE p.board_id = ?
";
$types  = "i";
$params = [$board_id];

if ($title !== '') {
    $sql .= " AND p.title   LIKE ?";
    $params[] = "%$title%";  $types .= "s";
}
if ($author !== '') {
    $sql .= " AND u.username LIKE ?";
    $params[] = "%$author%"; $types .= "s";
}
$sql .= " ORDER BY p.id $order";

echo "<pre>";
var_dump("--- SQL 쿼리 준비 전 ---");
var_dump("SQL:", $sql);
var_dump("Types:", $types);
var_dump("Params:", $params);
echo "</pre>";

// **여기서 오류가 발생할 가능성이 높습니다.**
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "<pre>";
    var_dump("--- SQL 준비 실패 ---");
    var_dump("MySQL Error:", $conn->error);
    echo "</pre>";
    // 여기서 더 이상 진행하지 않고 exit 하여 에러를 명확히 봅니다.
    exit("SQL 준비 실패로 종료합니다.");
}

echo "<pre>";
var_dump("--- SQL 준비 성공 ---");
echo "</pre>";

$stmt->bind_param($types, ...$params);
echo "<pre>";
var_dump("--- bind_param 완료 ---");
echo "</pre>";

$stmt->execute();
if ($stmt->errno) {
    echo "<pre>";
    var_dump("--- SQL 실행 실패 ---");
    var_dump("MySQL Error:", $stmt->error);
    echo "</pre>";
    exit("SQL 실행 실패로 종료합니다.");
}
echo "<pre>";
var_dump("--- SQL 실행 성공 ---");
echo "</pre>";

$result = $stmt->get_result();

echo "<pre>";
var_dump("--- get_result 완료 ---");
echo "</pre>";

$total = $result->num_rows;
echo "<pre>";
var_dump("Total rows:", $total);
echo "</pre>";

// ... (이후 HTML 출력 부분)
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>👥 사용자 목록</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; padding: 40px; background: #f5f6fa; }
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background: #007bff; color: white; }
    tr:hover { background: #f0f0f0; }
    .ban-btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; }
    .ban { background: #dc3545; }
    .unban { background: #28a745; }
  </style>
  <script>
    function toggleBan(userId, action) {
      fetch('list.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'user_id=' + userId + '&action=' + action
      }).then(() => location.reload());
    }
  </script>
</head>
<body>
  <h1>👥 사용자 목록</h1>
  <table>
    <tr>
      <th>ID</th><th>이메일</th><th>가입일</th><th>관리자</th><th>차단상태</th><th>관리</th>
    </tr>
    <?php foreach ($users as $user): ?>
      <tr>
        <td><?= htmlspecialchars($user["id"]) ?></td>
        <td><?= htmlspecialchars($user["email"]) ?></td>
        <td><?= htmlspecialchars($user["created_at"] ?? '-') ?></td>
        <td><?= $user["is_admin"] ? "✅" : "❌" ?></td>
        <td><?= $user["is_banned"] ? "⛔ 차단됨" : "✅ 정상" ?></td>
        <td>
          <?php if (!$user["is_admin"]): ?>
            <button class="ban-btn <?= $user["is_banned"] ? 'unban' : 'ban' ?>"
                    onclick="toggleBan(<?= $user['id'] ?>, '<?= $user['is_banned'] ? 'unban' : 'ban' ?>')">
              <?= $user["is_banned"] ? '차단 해제' : '차단' ?>
            </button>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
