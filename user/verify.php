<?php
require_once "../db.php";

$email = trim($_POST["email"] ?? '');
$input_code = trim($_POST["code"] ?? '');

if (!$email || !$input_code) {
    die("이메일과 인증번호를 모두 입력해주세요.");
}

// 차단 여부 확인
$stmt = $conn->prepare("SELECT * FROM banned_emails WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    die("해당 이메일은 차단되었습니다.");
}

// 최근 인증번호 가져오기
$stmt = $conn->prepare("SELECT * FROM auth_codes WHERE email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$code = $result->fetch_assoc();

if (!$code) {
    die("해당 이메일로 요청된 인증번호가 없습니다.");
}
if ($code["used"]) {
    die("이미 사용된 인증번호입니다.");
}

// 일치 확인
if ($code["code"] === $input_code) {
    // 성공: 사용 처리
    $stmt = $conn->prepare("UPDATE auth_codes SET used = 1, fail_count = 0 WHERE id = ?");
    $stmt->bind_param("i", $code["id"]);
    $stmt->execute();

    // 인증된 이메일 저장 (회원가입 가능 이메일)
    $stmt = $conn->prepare("INSERT IGNORE INTO verified_emails (email) VALUES (?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    echo "인증이 완료되었습니다. 이제 회원가입을 진행할 수 있습니다.";
} else {
    // 실패 처리
    $new_fail = $code["fail_count"] + 1;
    $stmt = $conn->prepare("UPDATE auth_codes SET fail_count = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_fail, $code["id"]);
    $stmt->execute();

    if ($new_fail >= 5) {
        $stmt = $conn->prepare("INSERT IGNORE INTO banned_emails (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        die("인증번호 5회 실패로 이메일이 차단되었습니다.");
    }

    die("인증번호가 일치하지 않습니다. 실패 횟수: $new_fail / 5");
}
?>