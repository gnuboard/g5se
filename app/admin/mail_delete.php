<?php
/*
 * /admin/mail_delete — 회원메일 일괄삭제 POST 핸들러.
 */
$sub_menu = '200300';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if (function_exists('check_demo')) {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (!$post_count_chk) {
    alert('삭제할 메일목록을 1개이상 선택해 주세요.');
}

for ($i = 0; $i < $post_count_chk; $i++) {
    $ma_id = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

    sql_pdo_query(" delete from {$g5['mail_table']} where ma_id = :ma_id ", [':ma_id' => $ma_id]);
    run_event('admin_mail_deleted', $ma_id);
}

header('Location: '.G5_ADMIN_URL.'/mail_list', true, 302);
exit;
