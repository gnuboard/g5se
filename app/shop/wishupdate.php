<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원 전용 서비스 입니다.', G5_BBS_URL.'/login.php?url='.urlencode($url));

if ($w == "d")
{
    $wi_id = isset($_GET['wi_id']) ? (int) $_GET['wi_id'] : 0;

    $row = sql_pdo_fetch(" select mb_id from {$g5['g5_shop_wish_table']} where wi_id = :wi_id ", [':wi_id' => $wi_id]);

    if($row['mb_id'] != $member['mb_id'])
        alert('위시리시트 상품을 삭제할 권한이 없습니다.');

    sql_pdo_query(" delete from {$g5['g5_shop_wish_table']} where wi_id = :wi_id and mb_id = :mb_id ",
                  [':wi_id' => $wi_id, ':mb_id' => $member['mb_id']]);
}
else
{
    $it_id = isset($_REQUEST['it_id']) ? $_REQUEST['it_id'] : null;

    if(is_array($it_id))
        $it_id = $_REQUEST['it_id'][0];

    $it_id = safe_replace_regex($it_id, 'it_id');

    if(!$it_id)
        alert('상품코드가 올바르지 않습니다.', G5_SHOP_URL);
    
    // 본인인증, 성인인증체크
    if(!$is_admin) {
        $msg = shop_member_cert_check($it_id, 'item');
        if($msg)
            alert($msg, G5_SHOP_URL);
    }

    // 상품정보 체크
    $row = get_shop_item($it_id, true);

    if(! (isset($row['it_id']) && $row['it_id']))
        alert('상품정보가 존재하지 않습니다.', G5_SHOP_URL);

    $row = sql_pdo_fetch(" select wi_id from {$g5['g5_shop_wish_table']} where mb_id = :mb_id and it_id = :it_id ",
                        [':mb_id' => $member['mb_id'], ':it_id' => $it_id]);

    if (! (isset($row['wi_id']) && $row['wi_id'])) { // 없다면 등록
        sql_pdo_query(" insert {$g5['g5_shop_wish_table']}
                            set mb_id = :mb_id, it_id = :it_id, wi_time = :wi_time, wi_ip = :wi_ip ",
                      [
                          ':mb_id'   => $member['mb_id'],
                          ':it_id'   => $it_id,
                          ':wi_time' => G5_TIME_YMDHIS,
                          ':wi_ip'   => $_SERVER['REMOTE_ADDR'],
                      ]);
    }
}

goto_url('./wishlist.php');