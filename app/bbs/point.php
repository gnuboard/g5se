<?php
include_once('./_common.php');

if ($is_guest)
    alert_close('회원만 조회하실 수 있습니다.');

$g5['title'] = get_text($member['mb_nick']).' 님의 포인트 내역';
include_once(G5_PATH.'/head.sub.php');

$list = array();

$sql_common = " from {$g5['point_table']} where mb_id = :mb_id ";
$common_params = [':mb_id' => $member['mb_id']];
$sql_order = " order by po_id desc ";

$row = sql_pdo_fetch(" select count(*) as cnt {$sql_common} ", $common_params);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$from_record_i = (int) $from_record;
$rows_i        = (int) $rows;

$result = sql_pdo_query(" select * {$sql_common} {$sql_order} limit {$from_record_i}, {$rows_i} ",
                        $common_params);

for ($i=0; $row=sql_pdo_fetch_array($result); $i++) {
    $list[] = $row;
}

include_once($member_skin_path.'/point.skin.php');

include_once(G5_PATH.'/tail.sub.php');