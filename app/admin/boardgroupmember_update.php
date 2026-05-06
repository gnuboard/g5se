<?php
$sub_menu = "300200";
require_once __DIR__.'/_common.php';
require_once __DIR__.'/_layout.php';
admin_require_login();
require_once __DIR__.'/admin.lib.php';

sql_query(" ALTER TABLE {$g5['group_member_table']} CHANGE `gm_id` `gm_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT ", false);

if ($w == '') {
    auth_check_menu($auth, $sub_menu, 'w');

    $mb = get_member($mb_id);
    if (empty($mb['mb_id'])) {
        alert('존재하지 않는 회원입니다.');
    }

    $gr = get_group($gr_id);
    if (empty($gr['gr_id'])) {
        alert('존재하지 않는 그룹입니다.');
    }

    $row = sql_pdo_fetch(" select count(*) as cnt from {$g5['group_member_table']} where gr_id = :gr_id and mb_id = :mb_id ",
                        [':gr_id' => $gr_id, ':mb_id' => $mb_id]);
    if ($row['cnt']) {
        alert('이미 등록되어 있는 자료입니다.');
    } else {
        check_admin_token();

        sql_pdo_query(" insert into {$g5['group_member_table']}
                            set gr_id = :gr_id, mb_id = :mb_id, gm_datetime = :gm_datetime ",
                      [
                          ':gr_id'       => $_POST['gr_id'],
                          ':mb_id'       => $_POST['mb_id'],
                          ':gm_datetime' => G5_TIME_YMDHIS,
                      ]);
    }
} elseif ($w == 'd' || $w == 'ld') {
    auth_check_menu($auth, $sub_menu, 'd');

    $count = count($_POST['chk']);
    if (!$count) {
        alert('삭제할 목록을 하나이상 선택해 주세요.');
    }

    check_admin_token();

    for ($i = 0; $i < $count; $i++) {
        $gm_id = (int) $_POST['chk'][$i];
        $gm = sql_pdo_fetch(" select * from {$g5['group_member_table']} where gm_id = :gm_id ", [':gm_id' => $gm_id]);
        if (!$gm['gm_id']) {
            if ($count == 1) {
                alert('존재하지 않는 자료입니다.');
            } else {
                continue;
            }
        }

        sql_pdo_query(" delete from {$g5['group_member_table']} where gm_id = :gm_id ", [':gm_id' => $gm_id]);
    }
}

if ($w == 'ld') {
    goto_url('./boardgroupmember_list.php?gr_id=' . $gr_id);
} else {
    goto_url('./boardgroupmember_form.php?mb_id=' . $mb_id);
}
