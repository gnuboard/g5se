<?php
/*
 * /admin/mail_update — 회원메일 저장/삭제 POST 핸들러.
 */
$sub_menu = "200300";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($w == 'u' || $w == 'd') {
    if (function_exists('check_demo')) {
        check_demo();
    }
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$ma_id = isset($_POST['ma_id']) ? (int) $_POST['ma_id'] : 0;
$ma_subject = isset($_POST['ma_subject']) ? strip_tags(clean_xss_attributes($_POST['ma_subject'])) : '';
$ma_content = isset($_POST['ma_content']) ? $_POST['ma_content'] : '';

if ($w == '') {
    $sql = " insert {$g5['mail_table']}
                set ma_subject = '{$ma_subject}',
                     ma_content = '{$ma_content}',
                     ma_time = '" . G5_TIME_YMDHIS . "',
                     ma_ip = '{$_SERVER['REMOTE_ADDR']}' ";
    sql_query($sql);

    $ma_id = sql_insert_id();
    run_event('admin_mail_created', $ma_id);

} elseif ($w == 'u') {
    $sql = " update {$g5['mail_table']}
                set ma_subject = '{$ma_subject}',
                     ma_content = '{$ma_content}',
                     ma_time = '" . G5_TIME_YMDHIS . "',
                     ma_ip = '{$_SERVER['REMOTE_ADDR']}'
                where ma_id = '{$ma_id}' ";
    sql_query($sql);
    run_event('admin_mail_updated', $ma_id);

} elseif ($w == 'd') {
    $sql = " delete from {$g5['mail_table']} where ma_id = '{$ma_id}' ";
    sql_query($sql);
    run_event('admin_mail_deleted', $ma_id);
}

header('Location: '.G5_ADMIN_URL.'/mail_list', true, 302);
exit;
