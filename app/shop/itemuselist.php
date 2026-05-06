<?php
include_once('./_common.php');

if( isset($sfl) && ! in_array($sfl, array('b.it_name', 'a.it_id', 'a.is_subject', 'a.is_content', 'a.is_name', 'a.mb_id')) ){
    //다른값이 들어가있다면 초기화
    $sfl = '';
}

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/itemuselist.php');
    return;
}

$g5['title'] = '사용후기';
include_once('./_head.php');

$sql_common = " from `{$g5['g5_shop_item_use_table']}` a join `{$g5['g5_shop_item_table']}` b on (a.it_id=b.it_id) ";
$sql_search = " where a.is_confirm = '1' ";

if(!$sfl)
    $sfl = 'b.it_name';

$search_params = [];
if ($stx) {
    switch ($sfl) {
        case "a.it_id" :
            $sql_search .= " and ($sfl like :stx) ";
            $search_params[':stx'] = $stx.'%';
            break;
        case "a.is_name" :
        case "a.mb_id" :
            $sql_search .= " and ($sfl = :stx) ";
            $search_params[':stx'] = $stx;
            break;
        default :
            $sql_search .= " and ($sfl like :stx) ";
            $search_params[':stx'] = '%'.$stx.'%';
            break;
    }
}

if (!$sst) {
    $sst  = "a.is_id";
    $sod = "desc";
}
$allowed_sst = ['a.is_id', 'a.is_subject', 'a.is_time', 'b.it_name', 'a.is_score'];
if (!in_array($sst, $allowed_sst, true)) $sst = 'a.is_id';
$sod = (strtolower($sod) === 'asc') ? 'asc' : 'desc';
$sql_order = " order by $sst $sod ";

$row = sql_pdo_fetch(" select count(*) as cnt $sql_common $sql_search $sql_order ", $search_params);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$result = sql_pdo_query(" select * $sql_common $sql_search $sql_order limit ".(int)$from_record.', '.(int)$rows.' ',
                       $search_params);

$itemuselist_skin = G5_SHOP_SKIN_PATH.'/itemuselist.skin.php';

if(!file_exists($itemuselist_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemuselist_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemuselist_skin);
}

include_once('./_tail.php');