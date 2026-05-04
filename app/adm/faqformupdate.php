<?php
$sub_menu = '300700';
include_once('./_common.php');

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

$sql_set    = " fa_subject = :fa_subject, fa_content = :fa_content, fa_order = :fa_order ";
$sql_params = [
    ':fa_subject' => $fa_subject,
    ':fa_content' => $fa_content,
    ':fa_order'   => $fa_order,
];

if ($w == "")
{
    sql_pdo_query(
        " insert {$g5['faq_table']} set fm_id = :fm_id, {$sql_set} ",
        array_merge([':fm_id' => $fm_id], $sql_params)
    );

    $fa_id = sql_insert_id();
    run_event('admin_faq_item_created', $fa_id, $fm_id);
}
else if ($w == "u")
{
    sql_pdo_query(
        " update {$g5['faq_table']} set {$sql_set} where fa_id = :fa_id ",
        array_merge($sql_params, [':fa_id' => $fa_id])
    );
    run_event('admin_faq_item_updated', $fa_id, $fm_id);
}
else if ($w == "d")
{
    sql_pdo_query(" delete from {$g5['faq_table']} where fa_id = :fa_id ", [':fa_id' => $fa_id]);
    run_event('admin_faq_item_deleted', $fa_id, $fm_id);
}

if ($w == 'd')
    goto_url("./faqlist.php?fm_id=$fm_id");
else
    goto_url("./faqform.php?w=u&amp;fm_id=$fm_id&amp;fa_id=$fa_id");