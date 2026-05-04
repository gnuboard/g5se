<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

$delete_token = get_session('ss_memo_delete_token');
set_session('ss_memo_delete_token', '');

if (!($token && $delete_token == $token))
    alert('토큰 에러로 삭제 불가합니다.');

$me_id = isset($_REQUEST['me_id']) ? (int) $_REQUEST['me_id'] : 0;

$row = sql_pdo_fetch(" select * from {$g5['memo_table']} where me_id = :me_id ",
                     [':me_id' => $me_id]);

sql_pdo_query(" delete from {$g5['memo_table']}
                where me_id = :me_id
                and (me_recv_mb_id = :mb_id or me_send_mb_id = :mb_id) ",
              [':me_id' => $me_id, ':mb_id' => $member['mb_id']]);

if (!$row['me_read_datetime'][0]) // 메모 받기전이면
{
    sql_pdo_query(" update {$g5['member_table']} set mb_memo_call = ''
                    where mb_id = :recv_id and mb_memo_call = :send_id ",
                  [':recv_id' => $row['me_recv_mb_id'], ':send_id' => $row['me_send_mb_id']]);

    sql_pdo_query(" update `{$g5['member_table']}` set mb_memo_cnt = :cnt where mb_id = :mb_id ",
                  [':cnt' => get_memo_not_read($member['mb_id']), ':mb_id' => $member['mb_id']]);
}

run_event('memo_delete', $me_id, $row);

goto_url('./memo.php?kind='.$kind);