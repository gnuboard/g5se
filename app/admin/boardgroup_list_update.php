<?php
$sub_menu = "300200";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$post_chk       = isset($_POST['chk']) ? (array) $_POST['chk'] : array();
$post_group_id  = isset($_POST['group_id']) ? (array) $_POST['group_id'] : array();
$act_button     = isset($_POST['act_button']) ? $_POST['act_button'] : '';

$chk_count = count($post_chk);

if (!$chk_count) {
    alert($act_button . '할 게시판그룹을 1개이상 선택해 주세요.');
}

for ($i = 0; $i < $chk_count; $i++) {
    $k              = isset($post_chk[$i]) ? (int) $post_chk[$i] : 0;
    $gr_id          = preg_replace('/[^a-z0-9_]/i', '', $post_group_id[$k]);
    $gr_subject     = isset($_POST['gr_subject'][$k]) ? strip_tags(clean_xss_attributes($_POST['gr_subject'][$k])) : '';
    $gr_admin       = isset($_POST['gr_admin'][$k]) ? strip_tags(clean_xss_attributes($_POST['gr_admin'][$k])) : '';
    $gr_use_access  = isset($_POST['gr_use_access'][$k]) ? (int) $_POST['gr_use_access'][$k] : 0;
    $gr_order       = isset($_POST['gr_order'][$k]) ? (int) $_POST['gr_order'][$k] : 0;

    if ($act_button == '선택수정') {
        $sql = " update {$g5['group_table']}
                    set gr_subject    = :gr_subject,
                        gr_admin      = :gr_admin,
                        gr_use_access = :gr_use_access,
                        gr_order      = :gr_order
                  where gr_id         = :gr_id ";
        $params = [
            ':gr_subject'    => $gr_subject,
            ':gr_admin'      => $gr_admin,
            ':gr_use_access' => $gr_use_access,
            ':gr_order'      => $gr_order,
            ':gr_id'         => $gr_id,
        ];
        if ($is_admin != 'super') {
            $sql .= " and gr_admin = :gr_admin_chk ";
            $params[':gr_admin_chk'] = $gr_admin;
        }
        sql_pdo_query($sql, $params);
    } elseif ($act_button == '선택삭제') {
        $row = sql_pdo_fetch(" select count(*) as cnt from {$g5['board_table']} where gr_id = :gr_id ", [':gr_id' => $gr_id]);
        if ($row['cnt']) {
            alert("이 그룹에 속한 게시판이 존재하여 게시판 그룹을 삭제할 수 없습니다.\\n\\n이 그룹에 속한 게시판을 먼저 삭제하여 주십시오.", G5_ADMIN_URL.'/board_list?sfl=gr_id&amp;stx=' . $gr_id);
        }

        // 그룹 삭제
        sql_pdo_query(" delete from {$g5['group_table']}        where gr_id = :gr_id ", [':gr_id' => $gr_id]);
        // 그룹접근 회원 삭제
        sql_pdo_query(" delete from {$g5['group_member_table']} where gr_id = :gr_id ", [':gr_id' => $gr_id]);
    }
}

run_event('admin_boardgroup_list_update', $act_button, $post_chk, $post_group_id, $qstr);

goto_url('./boardgroup_list.php?' . $qstr);
