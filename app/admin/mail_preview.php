<?php
/*
 * /admin/mail_preview — 회원메일 발송 미리보기 (새 창에 단독 HTML 출력).
 */
$sub_menu = "200300";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';
require_once G5_LIB_PATH . '/mailer.lib.php';

auth_check_menu($auth, $sub_menu, 'r');

$ma_id = isset($_REQUEST['ma_id']) ? (int) $_REQUEST['ma_id'] : 0;

$se = sql_pdo_fetch("select ma_subject, ma_content from {$g5['mail_table']} where ma_id = '{$ma_id}' ");

$subject = $se['ma_subject'];
$content = conv_content($se['ma_content'], 1) . "<hr size=0><p><span style='font-size:9pt; font-family:굴림'>▶ 더 이상 정보 수신을 원치 않으시면 [<a href='" . G5_URL . "/email_stop?mb_id=***&amp;mb_md5=***' target='_blank'>수신거부</a>] 해 주십시오.</span></p>";
?>

<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <title><?php echo G5_VERSION ?> 메일발송 테스트</title>
</head>

<body>
    <h1><?php echo $subject; ?></h1>
    <p><?php echo $content; ?></p>
    <p>
        <strong>주의!</strong> 이 화면에 보여지는 디자인은 실제 내용이 발송되었을 때 디자인과 다를 수 있습니다.
    </p>
</body>

</html>
