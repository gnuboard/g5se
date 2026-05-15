<?php
include_once('./_common.php');

$ms_id = isset($_REQUEST['ms_id']) ? (int) $_REQUEST['ms_id'] : 0;

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

sql_pdo_query(" delete from {$g5['scrap_table']} where mb_id = :mb_id and ms_id = :ms_id ",
              [':mb_id' => $member['mb_id'], ':ms_id' => $ms_id]);

sql_pdo_query(" update `{$g5['member_table']}` set mb_scrap_cnt = :cnt where mb_id = :mb_id ",
              [':cnt' => get_scrap_totals($member['mb_id']), ':mb_id' => $member['mb_id']]);

goto_url(G5_URL.'/scrap?page='.$page);
