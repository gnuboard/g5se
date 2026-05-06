<?php
include_once("./_common.php");

$bn_id = isset($_GET['bn_id']) ? (int) $_GET['bn_id'] : 0;

$row = sql_pdo_fetch(" select bn_id, bn_url from {$g5['g5_shop_banner_table']} where bn_id = :bn_id ", [':bn_id' => $bn_id]);

if( ! $row['bn_id'] ){
    alert('해당 배너가 존재하지 않습니다.', G5_SHOP_URL);
}

if ($_COOKIE['ck_bn_id'] != $bn_id)
{
    sql_pdo_query(" update {$g5['g5_shop_banner_table']} set bn_hit = bn_hit + 1 where bn_id = :bn_id ", [':bn_id' => $bn_id]);
    // 하루 동안
    set_cookie("ck_bn_id", $bn_id, 60*60*24);
}

$url = clean_xss_tags($row['bn_url']);

goto_url($url);