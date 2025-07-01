<?php
session_start();
require_once "../db.php";
require_once "../user/auth_check.php";

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 요청 검증
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        die("⚠️ CSRF 토큰 검증 실패");
    }
}

// 게시글 조회
$id = $_GET['id'] ?? '';
if (!$id) {
    echo "❌ 잘못된 접근입니다.";
    exit;
}

$stmt = $conn->prepare("SELECT p.*, u.username FROM board2_posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "❌ 게시글을 찾을 수 없습니다.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($post['title']) ?></title>
  <style>
    body {
      font-family: 'Pretendard', sans-serif;
      margin: 40px;
      background-color: #f9f9f9;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 { margin-bottom: 10px; }
    .meta { color: #777; font-size: 14px; margin-bottom: 20px; }
    .content { font-size: 16px; line-height: 1.6; margin-bottom: 20px; }
    .file-download-box { margin-top: 15px; }
    .action-buttons button {
      background: #007bff;
      color: white;
      padding: 7px 14px;
      margin-right: 8px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .action-buttons button:hover { background: #0056b3; }
    .back-link { display: inline-block; margin-top: 20px; color: #333; text-decoration: none; }
    .comment-box input[type="text"] {
      width: 80%;
      padding: 8px;
      margin-right: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    .comment-box button {
      padding: 8px 14px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .comment-box button:hover { background: #218838; }
  </style>
</head>
<body>
  <div class="container">
    <<h2><?= htmlspecialchars($post['title']) ?></h2>
    <div class="meta">작성자: <?= htmlspecialchars($post['username']) ?> | 작성일: <?= $post['created_at'] ?></div>
    <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

    <?php if ($post["filename"]): ?>
      <div class="file-download-box">
        📎 첨부 파일:
        <a href="download.php?id=<?= $post["id"] ?>" class="download-btn">
          <?= htmlspecialchars($post["filename"]) ?>
        </a>
      </div>
    <?php endif; ?>

    <!-- ✅ 삭제/수정 버튼 -->
    <?php if ($_SESSION['user_id'] == $post['user_id'] || ($_SESSION['is_admin'] ?? false)): ?>
      <div class="action-buttons" style="margin-top: 15px;">
        <form method="post" action="delete.php" onsubmit="return confirm('정말 삭제하시겠습니까?');" style="display:inline;">
          <input type="hidden" name="id" value="<?= $post['id'] ?>">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <button type="submit">삭제</button>
        </form>

        <a href="edit.php?id=<?= $post['id'] ?>">
          <button type="button">수정</button>
        </a>
      </div>
    <?php endif; ?>

<a href="list.php" class="back-link">← 목록으로</a>

    <!-- 댓글 작성 및 출력 -->
    <div class="comment-section">
      <h3>💬 댓글</h3>
      <form method="post" action="comment_insert.php">
        <input type="hidden" name="post_id" value="<?= $post["id"] ?>">
        <div class="comment-box">
          <input type="text" name="comment" placeholder="댓글을 입력하세요" required>
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <button type="submit">댓글 작성</button>
        </div>
      </form>

      <div class="comment-list" style="margin-top: 20px;">
        <?php
        $stmt = $conn->prepare("SELECT c.*, u.username FROM board2_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
        $stmt->bind_param("i", $post['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($comment = $result->fetch_assoc()):
        ?>
          <div class="comment-item" style="padding: 10px 0; border-bottom: 1px solid #eee;">
            <strong><?= htmlspecialchars($comment['username']) ?></strong>
            <span style="color: gray; font-size: 12px;">(<?= $comment['created_at'] ?>)</span>
            <div style="margin: 5px 0;"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>

            <?php if ($_SESSION['user_id'] == $comment['user_id'] || ($_SESSION['is_admin'] ?? false)): ?>
              <div style="margin-top: 5px;">
                <form method="post" action="comment_delete.php" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                  <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                  <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <button type="submit" style="font-size:12px; color: red; border: none; background: none; cursor: pointer;">삭제</button>
                </form>
                <form method="get" action="comment_edit.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                  <button type="submit" style="font-size:12px; color: blue; border: none; background: none; cursor: pointer;">수정</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</body>
</html>