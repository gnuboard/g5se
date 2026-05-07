<?php
// gnu5se: 통합 마이페이지 entry. 커뮤니티/쇼핑 모두 같은 hub 사용.
include_once('./_common.php');

if (!$is_member)
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_URL.'/mypage'));

$g5['title'] = '마이페이지';

// 회원 통계 — 통합 hub 카드용
$_my = [
    'point'   => isset($member['mb_point']) ? (int)$member['mb_point'] : 0,
    'mb_id'   => $member['mb_id'],
    'mb_name' => isset($member['mb_name']) ? get_text($member['mb_name']) : '',
    'mb_nick' => isset($member['mb_nick']) ? get_text($member['mb_nick']) : '',
    'mb_email'=> isset($member['mb_email']) ? get_text($member['mb_email']) : '',
    'mb_datetime' => isset($member['mb_datetime']) ? $member['mb_datetime'] : '',
];

// 활동 카운트 — silent fetch (테이블 없거나 NULL 이면 0)
$_my_count = [
    'scrap' => 0, 'memo' => 0, 'memo_unread' => 0,
    'cart'  => 0, 'wish' => 0, 'order' => 0, 'coupon' => 0, 'address' => 0,
];

$_r = @sql_pdo_fetch("select count(*) c from {$g5['scrap_table']} where mb_id = :mb_id", [':mb_id' => $_my['mb_id']]);
$_my_count['scrap'] = (int)($_r['c'] ?? 0);

$_r = @sql_pdo_fetch("select count(*) c from {$g5['memo_table']} where me_recv_mb_id = :mb_id and me_type = 'recv'", [':mb_id' => $_my['mb_id']]);
$_my_count['memo'] = (int)($_r['c'] ?? 0);

$_r = @sql_pdo_fetch("select count(*) c from {$g5['memo_table']} where me_recv_mb_id = :mb_id and me_type = 'recv' and me_read_datetime = '0000-00-00 00:00:00'", [':mb_id' => $_my['mb_id']]);
$_my_count['memo_unread'] = (int)($_r['c'] ?? 0);

if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
    // 장바구니 (선택된 + 미주문 ct)
    $_cart_id = function_exists('get_session') ? get_session('ss_cart_id') : '';
    if ($_cart_id) {
        $_r = @sql_pdo_fetch("select count(*) c from {$g5['g5_shop_cart_table']} where od_id = :od_id", [':od_id' => $_cart_id]);
        $_my_count['cart'] = (int)($_r['c'] ?? 0);
    }
    $_r = @sql_pdo_fetch("select count(*) c from {$g5['g5_shop_wish_table']} where mb_id = :mb_id", [':mb_id' => $_my['mb_id']]);
    $_my_count['wish'] = (int)($_r['c'] ?? 0);
    $_r = @sql_pdo_fetch("select count(*) c from {$g5['g5_shop_order_table']} where mb_id = :mb_id", [':mb_id' => $_my['mb_id']]);
    $_my_count['order'] = (int)($_r['c'] ?? 0);
    $_r = @sql_pdo_fetch("select count(*) c from {$g5['g5_shop_order_address_table']} where mb_id = :mb_id", [':mb_id' => $_my['mb_id']]);
    $_my_count['address'] = (int)($_r['c'] ?? 0);

    // 쿠폰 — 만료 안 된 사용 가능 쿠폰
    $_r = @sql_pdo_query("
        select cp_id from {$g5['g5_shop_coupon_table']}
        where mb_id IN (:mb_id, '전체회원')
          and cp_start <= :today and cp_end >= :today
    ", [':mb_id' => $_my['mb_id'], ':today' => G5_TIME_YMD]);
    $_cp_total = 0;
    if ($_r) {
        while ($_cp = sql_fetch_array($_r)) {
            if (function_exists('is_used_coupon') && !is_used_coupon($_my['mb_id'], $_cp['cp_id']))
                $_cp_total++;
        }
    }
    $_my_count['coupon'] = $_cp_total;
}

// 테마 오버라이드 — modern 카드 hub
if (defined('G5_THEME_PATH') && is_file(G5_THEME_PATH.'/mypage.php')) {
    require_once(G5_THEME_PATH.'/mypage.php');
    return;
}

// fallback (테마 없을 때) — 단순 텍스트
include_once(G5_PATH.'/head.sub.php');
echo '<h1>'.$g5['title'].'</h1>';
echo '<p>로그인: '.$_my['mb_id'].'</p>';
include_once(G5_PATH.'/tail.sub.php');
