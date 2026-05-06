<?php
include_once('./_common.php');

if( isset($sfl) && ! in_array($sfl, array('b.it_name', 'a.it_id', 'a.iq_subject', 'a.iq_question', 'a.iq_name', 'a.mb_id')) ){
    //다른값이 들어가있다면 초기화
    $sfl = '';
}

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/itemqalist.php');
    return;
}

$g5['title'] = '상품문의';
include_once('./_head.php');

$sql_common = " from `{$g5['g5_shop_item_qa_table']}` a join `{$g5['g5_shop_item_table']}` b on (a.it_id=b.it_id) ";
$sql_search = " where (1) ";

if(!$sfl)
    $sfl = 'b.it_name';

$search_params = [];
if ($stx) {
    // $sfl 은 화이트리스트 통과 (라인 4) — 컬럼명 안전
    switch ($sfl) {
        case "a.it_id" :
            $sql_search .= " and ($sfl like :stx) ";
            $search_params[':stx'] = $stx.'%';
            break;
        case "a.iq_name" :
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
    $sst  = "a.iq_id";
    $sod = "desc";
}
// $sst/$sod 도 컬럼명 — 외부 입력은 sfl 검증 흐름과 동일하게 화이트리스트 보호 필요
$allowed_sst = ['a.iq_id', 'a.iq_subject', 'a.iq_time', 'b.it_name'];
if (!in_array($sst, $allowed_sst, true)) $sst = 'a.iq_id';
$sod = (strtolower($sod) === 'asc') ? 'asc' : 'desc';
$sql_order = " order by $sst $sod ";

$row = sql_pdo_fetch(" select count(*) as cnt $sql_common $sql_search $sql_order ", $search_params);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$result = sql_pdo_query(" select a.*, b.it_name $sql_common $sql_search $sql_order limit ".(int)$from_record.', '.(int)$rows.' ',
                       $search_params);

$itemqalist_skin = G5_SHOP_SKIN_PATH.'/itemqalist.skin.php';

if(!file_exists($itemqalist_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemqalist_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemqalist_skin);
}

include_once('./_tail.php');