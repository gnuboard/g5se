<?php
$sub_menu = '300700';
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

if ($w == "u" || $w == "d")
    check_demo();

if ($w == 'd')
    auth_check_menu($auth, $sub_menu, "d");
else
    auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$fm_id = isset($_REQUEST['fm_id']) ? (int) $_REQUEST['fm_id'] : 0;
$fa_id = isset($_REQUEST['fa_id']) ? (int) $_REQUEST['fa_id'] : 0;
$fa_subject = isset($_POST['fa_subject']) ? $_POST['fa_subject'] : '';
$fa_content = isset($_POST['fa_content']) ? $_POST['fa_content'] : '';
$fa_order = isset($_POST['fa_order']) ? (int) $_POST['fa_order'] : 0;

$sql_common = " fa_subject = :fa_subject,
                fa_content = :fa_content,
                fa_order   = :fa_order ";
$common_params = [
    ':fa_subject' => $fa_subject,
    ':fa_content' => $fa_content,
    ':fa_order'   => $fa_order,
];

if ($w == "") {
    sql_pdo_query(" insert {$g5['faq_table']} set fm_id = :fm_id, $sql_common ",
                  array_merge($common_params, [':fm_id' => $fm_id]));
    $fa_id = sql_insert_id();
    run_event('admin_faq_item_created', $fa_id, $fm_id);
} else if ($w == "u") {
    sql_pdo_query(" update {$g5['faq_table']} set $sql_common where fa_id = :fa_id ",
                  array_merge($common_params, [':fa_id' => $fa_id]));
    run_event('admin_faq_item_updated', $fa_id, $fm_id);
} else if ($w == "d") {
    sql_pdo_query(" delete from {$g5['faq_table']} where fa_id = :fa_id ", [':fa_id' => $fa_id]);
    run_event('admin_faq_item_deleted', $fa_id, $fm_id);
}

if ($w == 'd') {
    header('Location: '.G5_ADMIN_URL."/faqlist?fm_id=$fm_id", true, 302); exit;
} else {
    header('Location: '.G5_ADMIN_URL."/faqform?w=u&fm_id=$fm_id&fa_id=$fa_id", true, 302); exit;
}