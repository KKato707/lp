<?php
/* =====================================================================
   お問い合わせフォーム送信用（index.html と同じフォルダに置く）
   ※PHPが動くレンタルサーバー（Xserver、ロリポップ等）で動作します。
   ===================================================================== */

// ★【変更】受信するメールアドレス（お店側）
$TO_EMAIL = 'info@example.com';
// ★【変更】送信元アドレス（サーバーと同じドメインのアドレス推奨。迷惑メール対策）
$FROM_EMAIL = 'no-reply@example.com';
// ★【変更】会社名
$COMPANY = 'Paint Studio ◯◯';

header('Content-Type: application/json; charset=UTF-8');
mb_language('Japanese');
mb_internal_encoding('UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['ok' => false, 'error' => 'method']); exit;
}
// スパム対策（隠し項目に入力があればボット扱い）
if (!empty($_POST['website'])) { echo json_encode(['ok' => true]); exit; }

$clean = fn($v, $len) => mb_substr(trim(str_replace(["\r", "\0"], '', (string)$v)), 0, $len);
$name     = $clean($_POST['name'] ?? '', 50);
$contact  = $clean($_POST['contact'] ?? '', 100);
$message  = $clean($_POST['message'] ?? '', 2000);
$estimate = $clean($_POST['estimate'] ?? '', 500);

if ($name === '' || $contact === '') {
  echo json_encode(['ok' => false, 'error' => 'required']); exit;
}

$subject = "【{$COMPANY}】Webお問い合わせ：{$name} 様";
$body = "Webサイトからお問い合わせがありました。\n\n"
      . "お名前：{$name}\n連絡先：{$contact}\n\n"
      . "ご相談内容：\n{$message}\n\n"
      . ($estimate !== '' ? "{$estimate}\n\n" : '')
      . "送信日時：" . date('Y-m-d H:i') . "\n";

$headers = "From: " . mb_encode_mimeheader($COMPANY) . " <{$FROM_EMAIL}>";
// 連絡先がメールアドレスなら、返信先に設定
if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
  $headers .= "\r\nReply-To: {$contact}";
}

$ok = mb_send_mail($TO_EMAIL, $subject, $body, $headers);
echo json_encode(['ok' => (bool)$ok]);
