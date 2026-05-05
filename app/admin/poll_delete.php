<?php
/*
 * /admin/poll_delete — 투표 일괄삭제 POST 핸들러.
 */
$sub_menu = "200900";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if (function_exists('check_demo')) {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (!$count) {
    alert('삭제할 투표목록을 1개이상 선택해 주세요.');
}

for ($i = 0; $i < $count; $i++) {
    $po_id = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

    $sql = " delete from {$g5['poll_table']} where po_id = '$po_id' ";
    sql_query($sql);

    $sql = " delete from {$g5['poll_etc_table']} where po_id = '$po_id' ";
    sql_query($sql);
}

header('Location: '.G5_ADMIN_URL.'/poll_list?' . $qstr, true, 302);
exit;
